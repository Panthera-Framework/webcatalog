<?php
/**
  * Web catalog management module
  *
  * @package Panthera\webcatalog\modules\webcatalog
  * @author Damian Kęska
  * @license GNU Affero General Public License 3, see license.txt
  */


/**
  * Web catalog management
  *
  * @package Panthera\webcatalog\modules\webcatalog
  * @author Damian Kęska
  */

class webCatalog
{
    protected static $categories = null;
    
    /**
      * Crontab job for keeping stats updated - Google Page Rank, and page online/offline status
      *
      * @author Damian Kęska
      */
    
    public static function cronJob($data)
    {
        global $panthera;
        
        // pagerank checker
        $panthera -> importModule('googlepr');
        
        $time = (time()-$panthera->config->getKey('webcatalog.checktime', 259200, 'int', 'webcatalog')); // 72 hours by default 
        $SQL = $panthera -> db -> query ('SELECT * FROM `{$db_prefix}webcatalog` WHERE `status_checktime` < ' .$time. ' ORDER BY `status_checktime` DESC LIMIT 0,10');
        $items = $SQL -> fetchAll(PDO::FETCH_ASSOC);

        foreach ($items as $item)
        {
            $i = new webCatalogItem('id', $item['id']);
        
            $f = fsockopen(parse_url($item['address'], PHP_URL_HOST), 80, $errno, $errstr, 5);
            
            if ($f)
            {
                fclose($f);
            } else {
                $i -> status = '0';
            }
            
            $i -> pr_google = GooglePR::getRank(parse_url($item['address'], PHP_URL_HOST));
            $i -> status_checktime = time();
            $i -> save();
            unset($i);
        }
    
        return $data;
    }
    
    /**
      * Get all categories
      *
      * @return array 
      * @author Damian Kęska
      */

    public static function getCategories()
    {
        global $panthera;
        
        if (self::$categories)
        {
            return self::$categories;
        }
        
        $SQL = $panthera -> db -> query ('SELECT * FROM `{$db_prefix}webcatalog_categories`');
        
        self::$categories = array();
        
        foreach ($SQL -> fetchAll(PDO::FETCH_ASSOC) as $category)
        {
            self::$categories[$category['id']] = $category['title'];
        }
        
        return self::$categories;
    }
    
    /**
      * Fetch all webcatalog links
      *
      * @param string|object|category $category Fetch links only from this category, also whereClause object can be passed
      * @return mixed 
      * @author Damian Kęska
      */

    public static function fetch($category='', $limit='', $limitFrom=0, $orderBy='id', $order='DESC', $outputObject='')
    {
        global $panthera;
        
        $categories = self::getCategories();
        $outputType = '';
        $by = '';
        
        if ($outputObject)
        {
            $outputType = 'webCatalogItem';
        }
        
        if ($category)
        {
            if (is_string($category))
            {
                $by = array('category_id' => $category);
            } elseif (is_object($category) or is_array($category)) {
                $by = $category;
            }
        }
        
        $fetch = $panthera->db->getRows('webcatalog', $by, $limit, $limitFrom, $outputType, $orderBy, $order);
        
        if ($outputObject or $limit === False)
        {
            return $fetch;
        }
        
        $output = array();
        
        foreach ($fetch as $row)
        {
            $row['category'] = '';
            
            if (isset($categories[$row['category_id']]))
            {
                $row['category'] = $categories[$row['category_id']];
            }
        
            $output[$row['id']] = $row;
        }
        
        return $output;
    }
    
    /**
      * Add URL to database
      *
      * @param string $name
      * @param string $address
      * @param string $script
      * @param string|int $categor
      * @param int $price
      * @param int $smsPrice
      * @throws Exception ('Input address is not a valid URL', 1), ('Unknown category specified', 2), ('Please enter an item name', 3)
      * @return int|bool Returns created item ID or False 
      * @author Damian Kęska
      */

    public static function addItem($name, $address, $script, $category, $price=0, $smsPrice=0, $ispaid=0)
    {
        global $panthera;
    
        $categoryID = intval($category);
    
        if (is_string($category))
        {
            $category = strtolower($category);
            $categories = self::getCategories();
            
            foreach ($categories as $catID => $catName)
            {
                if (strtolower($catName) == $category)
                {
                    $categoryID = $catID;
                    break;
                }
            }
        }
        
        if (!$name)
        {
            throw new Exception('Please enter an item name', 3);
        }
        
        if (!filter_var($address, FILTER_VALIDATE_URL))
        {
            throw new Exception('Input address is not a valid URL', 1);
        }
        
        if ($categoryID === 0)
        {
            throw new Exception('Unknown category specified', 2);
        }
    
        $data = array(
            'name' => strip_tags($name),
            'address' => strip_tags($address),
            'base_script' => strip_tags($script),
            'category_id' => intval($categoryID),
            'price' => (float)$price,
            'price_sms' => (float)$smsPrice,
            'pr_google' => 0,
            'status' => 1,
            'ispaid' => intval($ispaid)
        );
        
        // this will build a query like INSERT INTO `{$db_prefix}webcatalog` (...)
        $queryData = $panthera -> db -> buildInsertString($data, False, 'webcatalog');
        $panthera -> db -> query($queryData['query'], $queryData['values']);
        
        $item = new webCatalogItem('address', $data['address']);
        
        if ($item->exists())
        {
            return $item->id;
        }
        
        return False;
    }
    
    /**
      * Remove item by ID
      *
      * @param int $id
      * @return bool 
      * @author Damian Kęska
      */
    
    public static function removeItem($id)
    {
        global $panthera;
        $item = new webCatalogItem('id', $id);
        
        if ($item -> exists())
        {
            $item -> clearCache();
            unset($item);
            $SQL = $panthera -> db -> query('DELETE FROM `{$db_prefix}webcatalog` WHERE `id` = :id', array('id' => intval($id)));
            return True;
        }
        
        return False;
    }
}

/**
  * webcatalog table model class
  *
  * @package Panthera\webcatalog\modules\webcatalog
  * @author Damian Kęska
  */

class webCatalogItem extends pantheraFetchDB
{
    protected $_tableName = 'webcatalog';
    protected $_idColumn = 'id';
    protected $_constructBy = array('id', 'array', 'address');
    protected $_unsetColumns = array();
}
