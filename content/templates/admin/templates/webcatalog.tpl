{$site_header}

<script type="text/javascript">
function removeItem(id)
{
    panthera.confirmBox.create('{function="localize('Are you sure you want to remove this link?', 'webcatalog')"}', function (response) {
        if (response == 'Yes')
        {
            panthera.jsonPOST({url: '?display=webcatalog&cat=admin&action=removeItem', data: 'id='+id, success: function (response) {
                    if (response.status == 'success')
                    {
                        $('#row_'+id).remove();
                    }
                }
            });
        }
    });
}
</script>

{include="ui.titlebar"}


<div id="topContent">
    {$uiSearchbarName="uiTop"}
    {include="ui.searchbar"}
    
    <div class="separatorHorizontal"></div>
    
    <div class="searchBarButtonArea">
        <div style="float: left; display: inline-block; margin-left: 10px;">
            <input type="button" value="Płatne" onclick="navigateTo('?{function="getQueryString('GET', 'price=paid', '_')"}')">
            <input type="button" value="Darmowe" onclick="navigateTo('?{function="getQueryString('GET', 'price=free', '_')"}')">
            <input type="button" value="Wszystkie" onclick="navigateTo('?{function="getQueryString('GET', '', 'price,_')"}')">
        </div>
        
        <input type="button" value="Zobacz statystyki" onclick="panthera.popup.toggle('element:#statsPopup')">
        <input type="button" value="Dodaj nowy link" onclick="panthera.popup.toggle('element:#addNewLinkPopup')">
        <input type="button" value="Eksportuj dane" onclick="panthera.popup.toggle('element:#exportDataPopup')">
    </div>
</div>

<div id="popupOverlay" style="text-align: center; padding-top: 20px;"></div>

<div class="ajax-content centeredObject" style="text-align: center; padding-left: 0px;">

    <div style="display: inline-block;">
        <table>
            <thead>
                <tr>
                    <th>id</th>
                    <th>{function="localize('Name', 'webcatalog')"}</th>
                    <th>{function="localize('URL address', 'webcatalog')"}</th>
                    <th>{function="localize('SMS Price', 'webcatalog')"}</th>
                    <th>{function="localize('Price', 'webcatalog')"}</th>
                    <th>{function="localize('Paid', 'webcatalog')"}</th>
                    <th>{function="localize('Google Pagerank', 'webcatalog')"}</th>
                    <th>{function="localize('Category', 'webcatalog')"}</th>
                    <th>{function="localize('Script', 'webcatalog')"}</th>
                    <th>&nbsp;</th>
                </tr>
            </thead>
            
            <tbody>
                {if="!$items"}
                <tr><td colspan="10" style="text-align: center;">{function="localize('No items to display', 'webcatalog')"}</td></tr>
                {/if}
                
                {if="$items"}
                {loop="$items"}
                <tr id="row_{$value.id}" style="position: relative;">
                    <!--<td>{$value.id}</td>-->
                    <td>{$value.id}</td>
                    <td><a href="#" onclick="panthera.popup.toggle('?display=webcatalog&cat=admin&action=editLink&id={$value.id}')">{$value.name}</a> {if="!$value.status"}&nbsp;<span style="color: red;"><i>(offline)</i></span>{/if}</td>
                    <td><a href="{$value.address}" target="blank">{$value.address}</a></td>
                    <td>{$value.price_sms} zł</td>
                    <td>{$value.price} zł</td>
                    <td>{if="$value.ispaid"}{function="localize('Yes')"}{else}{function="localize('No')"}{/if}</td>
                    <td>{$value.pr_google}</td>
                    <td><a href="?display=webcatalog&cat=admin&category={$value.category_id}" class="ajax_link">{$value.category}</a></a></td>
                    <td>{$value.base_script}</td>
                    <td>
                        <a href="#" onclick="removeItem('{$value.id}')">
                            <img src="{$PANTHERA_URL}/images/admin/ui/delete.png" style="max-height: 22px;" alt="{function="localize('Remove', 'messages')"}">
                        </a>
                        
                        <a href="#" onclick="panthera.popup.toggle('?display=webcatalog&cat=admin&action=editLink&id={$value.id}')">
                            <img src="{$PANTHERA_URL}/images/admin/ui/edit.png" style="max-height: 22px;" alt="{function="localize('Edit', 'messages')"}">
                        </a>
                    </td>
                </tr>
                {/loop}
                <tr><td colspan="3">Łącznie {$stats.count} elementów</td><td>{$stats.price_sms} zł</td><td>{$stats.price} zł</td><td colspan="5"></td></tr>
                {/if}
            </tbody>
        </table>
        
        <div style="position: relative; text-align: left;" class="pager">{$uiPagerName="webCatalog"}{include="ui.pager"}</div>
    </div>
</div>

<!-- Statistics popup -->
<div style="display: none;" id="statsPopup">
    <table style="width: 350px; margin: 0 auto;">
    <thead>
        <tr>
            <th colspan="2">{function="localize('Categories statistics', 'webcatalog')"}</th>
        </tr>
        
    </thead>
    
    <tbody>
        {loop="$stats.categories"}
        <tr>
            <td>{$key}:</td><td>{$value}</td>
        </tr>
        {/loop}
    </tbody>
    </table>
</div>

<!-- Exporting data popup -->
<div style="display: none;" id="exportDataPopup">
    <form action="?display=webcatalog&cat=admin&action=exportData" method="POST" id="exportDataForm">
        <table class="formTable" style="margin: 0 auto;">
            <thead>
                <tr>
                    <td colspan="2" class="formTableHeader" style="padding-top: 0px; padding-bottom: 30px;">
                        <p style="color: #e5ebef; padding: 0px; margin: 0px; margin-left: 30px;">{function="localize('Export data', 'webcatalog')"}</p>
                    </td>
                </tr>
            </thead>
            
            <tbody>
                <tr style="background-color: transparent;">
                    <th>{function="localize('Category', 'webcatalog')"}:</th>
                    <td>
                        <select name="category">
                            {loop="$categories"}
                            <option value="{$key}">{$value}</option>
                            {/loop}
                        </select>
                    </td>
                </tr>
                
                <tr style="background-color: transparent;">
                    <th>Działanie:</th>
                    <td>
                        <select name="status">
                            <option value="all"></option>
                            <option value="1">Online</option>
                            <option value="0">Offline</option>
                        </select>
                    </td>
                </tr>
                
                <tr style="background-color: transparent;">
                    <th>{function="localize('Google Pagerank', 'webcatalog')"}:</th>
                    <td><input type="text" name="pr_from" value="0" style="min-width: 20px;"> - <input type="text" name="pr_to" value="10" style="min-width: 20px;"></td>
                </tr>
                
                <tr style="background-color: transparent;">
                    <th>{function="localize('Script', 'webcatalog')"}:</th>
                    <td><input type="text" name="base_script" style="width: 280px;"></td>
                </tr>
            </tbody>
            
            <tfoot>
                <tr>
                    <td colspan="2" style="padding-top: 35px;">
                        <input type="button" value="{function="localize('Cancel')"}" onclick="panthera.popup.close()" style="float: left; margin-left: 30px;">
                        <input type="submit" value="{function="localize('Export', 'webcatalog')"}" style="float: right; margin-right: 30px;">
                    </td>
                </tr>
            </tfoot>
        </table>
    </form>
    
    <script type="text/javascript">
    $('#exportDataForm').on("submit", function () {
        panthera.jsonPOST({data: '#exportDataForm', messageBox: 'w2ui', success: function (response) {
                if (response.status == 'success')
                {
                    if(response.url)
                    {
                        window.location = response.url;
                    }
                }
        
            }
        });
    
        return false;
    });
    </script>
</div>


<!-- Adding new link popup -->
<div style="display: none;" id="addNewLinkPopup">
    <form action="?display=webcatalog&cat=admin&action=createNew" method="POST" id="createForm" name="createForm">
        <table class="formTable" style="margin: 0 auto;">
             <thead>
                 <tr>
                    <td colspan="2" class="formTableHeader" style="padding-top: 0px; padding-bottom: 30px;">
                        <p style="color: #e5ebef; padding: 0px; margin: 0px; margin-left: 30px;">Dodaj nowy link</p>
                    </td>
                 </tr>
             </thead>
             
              <tbody>
                    <tr>
                        <th>{function="localize('Name', 'webcatalog')"}:</th>
                        <td><input type="text" name="catalogName"></td>
                    </tr>
                    
                    <tr style="background-color: transparent;">
                        <th>{function="localize('URL address', 'webcatalog')"}:</th>
                        <td><input type="text" name="address"></td>
                    </tr>
                    
                    <tr>
                        <th>{function="localize('SMS Price', 'webcatalog')"}:</th>
                        <td><input type="text" name="price_sms" value="0"></td>
                    </tr>
                    
                    <tr style="background-color: transparent;">
                        <th>{function="localize('Price', 'webcatalog')"}:</th>
                        <td><input type="text" name="price" value="0"></td>
                    </tr>
                    
                    <tr>
                        <th>{function="localize('Paid', 'webcatalog')"}:</th>
                        <td>
                            <select name="ispaid">
                                <option value="0">{function="localize('No')"}</option>
                                <option value="1">{function="localize('Yes')"}</option>
                            </select>
                        </td>
                    </tr>
                    
                    <tr style="background-color: transparent;">
                        <th>{function="localize('Category', 'webcatalog')"}:</th>
                        <td>
                            <select name="category">
                                {loop="$categories"}
                                <option value="{$key}">{$value}</option>
                                {/loop}
                            </select>
                        </td>
                    </tr>
                    
                    <tr style="background-color: transparent;">
                        <th>{function="localize('Script', 'webcatalog')"}:</th><td><input type="text" name="base_script" value="własny"></td>
                    </tr>
              </tbody>
              
              <tfoot>
                    <tr>
                        <td colspan="2" style="padding-top: 35px;">
                            <input type="button" value="{function="localize('Cancel')"}" onclick="panthera.popup.close()" style="float: left; margin-left: 30px;">
                            <input type="submit" value="{function="localize('Add new', 'webcatalog')"}" style="float: right; margin-right: 30px;">
                        </td>
                    </tr>
              </tfoot>
        </table>
    </form>
    
    <script type="text/javascript">
    $('#createForm').on("submit", function () {
            panthera.jsonPOST({data: '#createForm', success: function (response) {
                    if (response.status == 'success')
                    {
                        navigateTo('?display=webcatalog&cat=admin');
                    }
                }
            });
        
            return false;
    });
    </script>
</div>
