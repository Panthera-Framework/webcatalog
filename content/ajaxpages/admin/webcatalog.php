<?php
/**
 * Custom pages manager
 *
 * @package Panthera\core\ajaxpages
 * @author Damian Kęska
 * @author Mateusz Warzyński
 * @license GNU Affero General Public License 3, see license.txt
 */

if (!defined('IN_PANTHERA'))
    exit;

// rights
if (!getUserRightAttribute($user, 'can_manage_webcatalog_links')) 
{
    $noAccess = new uiNoAccess; 
    $noAccess -> display();
}

$panthera -> locale -> loadDomain('search');
$panthera -> locale -> loadDomain('webcatalog');

// titlebar
$titleBar = new uiTitlebar();
$titleBar -> setTitle(localize('Webcatalog management', 'webcatalog'));

if ($_GET['action'] == 'createNew')
{
    try {
        webCatalog::addItem($_POST['catalogName'], $_POST['address'], $_POST['base_script'], $_POST['category'], $_POST['price'], $_POST['price_sms'], $_POST['ispaid']);
        ajax_exit(array('status' => 'success'));
    } catch (Exception $e) {
        ajax_exit(array('status' => 'failed', 'message' => localize($e->getMessage(), 'webcatalog')));
    }
} elseif ($_GET['action'] == 'removeItem') {

    if(webCatalog::removeItem(intval($_POST['id'])))
    {
        ajax_exit(array('status' => 'success'));
    }
    
    ajax_exit(array('status' => 'failed'));
} elseif ($_GET['action'] == 'editLink') {
    
    $item = new webCatalogItem('id', $_GET['id']);
    
    if (!$item->exists())
    {
        $panthera -> template -> display('no_page.tpl');
        pa_exit();
    }
    
    $panthera -> template -> push('name', $item->name);
    $panthera -> template -> push('category_id', $item->category_id);
    $panthera -> template -> push('categories', webCatalog::getCategories());
    $panthera -> template -> push('address', $item->address);
    $panthera -> template -> push('price', $item->price);
    $panthera -> template -> push('price_sms', $item->price_sms);
    $panthera -> template -> push('base_script', $item->base_script);
    $panthera -> template -> push('ispaid', (bool)intval($item->ispaid));
    $panthera -> template -> push('id', $item->id);
    
    $panthera -> template -> display('webcatalog_edititem.tpl');
    pa_exit();
    
/**
  * Saving item details
  *
  * @author Damian Kęska
  */
  
} elseif ($_GET['action'] == 'saveEditedItem') {

    $item = new webCatalogItem('id', $_POST['id']);
    
    if (!$item->exists())
    {
        $panthera -> template -> display('no_page.tpl');
        pa_exit();
    }
    
    $item -> name = $_POST['name'];
    $item -> category_id = intval($_POST['category']);
    $item -> price = (float)$_POST['price'];
    $item -> price_sms = (float)$_POST['price_sms'];
    $item -> base_script = strip_tags($_POST['base_script']);
    $item -> ispaid = intval($_POST['ispaid']);
    
    if (filter_var($_POST['address'], FILTER_VALIDATE_URL))
    {
        $item -> address = strip_tags($_POST['address']);
    }
    
    $item -> save();
    ajax_exit(array('status' => 'success'));
    
    
    /**
      * Data export
      *
      * @author Damian Kęska
      */
      
} elseif ($_GET['action'] == 'exportData') {
    $w = new whereClause();
    
    if($_POST['category'])
    {
        $w -> add('AND', 'category_id', '=', $_POST['category']);
    }
    
    if($_POST['status'])
    {
        if ($_POST['status'] == '1' or $_POST['status'] == '0')
            $w -> add('AND', 'status', '=', intval($_POST['status']));
    }
    
    if($_POST['pr_from'])
    {
        $w -> add('AND', 'pr_google', '>', $_POST['pr_from']);
    }
    
    if($_POST['pr_to'])
    {
        $w -> add('AND', 'pr_google', '<=', $_POST['pr_to']);
    }
    
    if($_POST['base_script'])
    {
        $w -> add('AND', 'base_script', '=', $_POST['base_script']);
    }
    
    $items = webCatalog::fetch($w);
    $rows = '';
    $header = '';
    $s = ';;';
    
    if (count($items) > 0)
    {
        foreach (end($items) as $key => $value)
        {
            if ($key == 'status_checktime')
                continue;
        
            $header .= $key. ' || ';
        }
        
        $header = rtrim($header, '| ');
        $header .= "\n===========================================================================================================================================\n";
    } else {
        ajax_exit(array('status' => 'failed'));
    }
    
    foreach ($items as $item)
    {
        unset($item['status_checktime']);
        $rows .= implode(';;', $item)."\n";
    }
    
    $sum = hash('md4', $header.$rows);
    $file = SITE_DIR. '/content/tmp/webcatalog-sum-' .$sum. '.txt';
    
    $fp = fopen($file, 'w');
    fwrite($fp, $header.$rows);
    fclose($fp);
    
    ajax_exit(array('status' => 'success', 'url' => pantheraUrl('{$PANTHERA_URL}/_ajax.php?display=webcatalog&cat=admin&action=downloadExportedData&hash=' .$sum. '&_bypass_x_requested_with=True', False, 'frontend')));
} elseif ($_GET['action'] == 'downloadExportedData') {
    $sum = addslashes($_GET['hash']);

    if (is_file(SITE_DIR. '/content/tmp/webcatalog-sum-' .$sum. '.txt'))
    {
        header('Content-type: application/octetstream');
        header('Content-Disposition: attachment; filename="webcatalog-export.txt"');
        print(file_get_contents(SITE_DIR. '/content/tmp/webcatalog-sum-' .$sum. '.txt'));
        pa_exit();
    } else {
        $panthera -> template -> display('no_page');
        pa_exit();
    }
}

$sBar = new uiSearchbar('uiTop');
//$sBar -> setMethod('POST');
$sBar -> setQuery($_GET['query']);
$sBar -> setAddress('?display=webcatalog&cat=admin&mode=search');
$sBar -> navigate(True);
$sBar -> addSetting('order', localize('Sort by', 'webcatalog'), 'select', array(
    'id' => array('title' => 'id', 'selected' => ($_GET['order'] == 'id')),
    'base_script' => array('title' => localize('Script', 'webcatalog'), 'selected' => ($_GET['order'] == 'base_script')),
    'price' => array('title' => localize('Price', 'webcatalog'), 'selected' => ($_GET['order'] == 'price')),
    'price_sms' => array('title' => localize('SMS Price', 'webcatalog'), 'selected' => ($_GET['order'] == 'price_sms')),
    'pr_google' => array('title' => localize('Google Pagerank', 'webcatalog'), 'selected' => ($_GET['order'] == 'google_pr')),
    'status' => array('title' => localize('Status', 'webcatalog'), 'selected' => ($_GET['order'] == 'status'))
));

$categories = webCatalog::getCategories();
$barCategories = array('' => array('title' => '', 'selected' => ($_GET['category'] == '')));

foreach ($categories as $categoryID => $categoryTitle)
{
    $barCategories[$categoryID] = array('title' => $categoryTitle, 'selected' => ($_GET['category'] == $categoryID));
}

$sBar -> addSetting('category', localize('Category', 'webcatalog'), 'select', $barCategories);
        
$sBar -> addSetting('direction', localize('Direction', 'search'), 'select', array(
    'ASC' => array('title' => localize('Ascending', 'search'), 'selected' => ($_GET['direction'] == 'ASC')),
    'DESC' => array('title' => localize('Descending', 'search'), 'selected' => ($_GET['direction'] == 'DESC'))
));

$sBar -> addSetting('price', localize('Price', 'webcatalog'), 'select', array(
    '' => array('title' => localize('All', 'webcatalog'), 'selected' => ($_GET['price'] == '')),
    'paid' => array('title' => localize('Paid', 'webcatalog'), 'selected' => ($_GET['price'] == 'paid')),
    'free' => array('title' => localize('Free', 'webcatalog'), 'selected' => ($_GET['price'] == 'free'))
));

$order = 'id';
$direction = 'DESC';
$orderColumns = array('id', 'base_script', 'price', 'price_sms', 'pr_google', 'status');

// order by
if (in_array($_GET['order'], $orderColumns))
{
    $order = $_GET['order'];
}
        
if ($_GET['direction'] == 'DESC' or $_GET['direction'] == 'ASC')
{
    $direction = $_GET['direction'];
}

$w = new whereClause();
        
if ($_GET['query'])
{
    $_GET['query'] = trim(strtolower($_GET['query'])); // strip unneeded spaces and make it lowercase
    $w -> add( 'OR', 'name', 'LIKE', '%' .$_GET['query']. '%');
    $w -> add( 'OR', 'address', 'LIKE', '%' .$_GET['query']. '%');
    $w -> add( 'OR', 'base_script', 'LIKE', '%' .$_GET['query']. '%');
}

if ($_GET['category'])
{
    $w -> setGroupStatement(2, 'AND');
    $w -> add('AND', 'category_id', '=', $_GET['category'], 2);
}

if ($_GET['price'])
{
    if ($_GET['price'] == 'paid')
    {
        $w -> setGroupStatement(2, 'AND');
        $w -> add('AND', 'ispaid', '=', 1, 2);
    } elseif($_GET['price'] == 'free') {
        $w -> setGroupStatement(2, 'AND');
        $w -> add('AND', 'ispaid', '=', 0, 2);
    }
}

$itemsCount = webCatalog::fetch($w, False, False);

$uiPager = new uiPager('webCatalog', $itemsCount, 'webCatalogAdmin');
$uiPager -> setActive($page);
$uiPager -> setLinkTemplatesFromConfig('webcatalog.tpl');

$items = webCatalog::fetch($w, $limit[0], $limit[1], $order, $direction);

$stats = array(
    'count' => count($items),
    'price' => 0,
    'price_sms' => 0,
    'categories' => array()
);

foreach ($items as $item)
{
    $stats['price'] += $item['price'];
    $stats['price_sms'] += $item['price_sms'];
    $stats['categories'][$item['category']]++;
}

$panthera -> template -> push('categories', $categories);
$panthera -> template -> push('stats', $stats);
$panthera -> template -> push('items', $items);
$panthera -> template -> display('webcatalog.tpl');
pa_exit();
