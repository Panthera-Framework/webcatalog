{$site_header}

<script type="text/javascript">
$(document).ready (function() {
    $('#saveEditedItemForm').submit(function () {
        panthera.jsonPOST({data: '#saveEditedItemForm', messageBox: 'w2ui', success: function (response) {
                if (response.status == 'success')
                {
                    navigateTo('?display=webcatalog&cat=admin');
                }
        
            }
        });
    
        return false;
    });
});
</script>

<div style="margin-top: 25px;">
<form action="?display=webcatalog&cat=admin&action=saveEditedItem" method="POST" id="saveEditedItemForm">
    <table class="formTable" style="margin: 0 auto;">
        <thead>
            <tr>
                <td colspan="2" class="formTableHeader" style="padding-top: 0px; padding-bottom: 30px;">
                    <p style="color: #e5ebef; padding: 0px; margin: 0px; margin-left: 30px;">{function="localize('Edit webcatalog link', 'webcatalog')"}</p>
                </td>
            </tr>
        </thead>
        
        <tbody>
            <tr style="background-color: transparent;">
                <th>{function="localize('Name', 'webcatalog')"}:</th><td><input type="text" name="name" value="{$name}"></td>
            </tr>
            
            <tr style="background-color: transparent;">
                <th>{function="localize('URL address', 'webcatalog')"}:</th><td><input type="text" name="address" value="{$address}"></td>
            </tr>
            
            <tr style="background-color: transparent;">
                <th>{function="localize('SMS Price', 'webcatalog')"}:</th><td><input type="text" name="price_sms" value="{$price_sms}"></td>
            </tr>
            
            <tr style="background-color: transparent;">
                <th>{function="localize('Price', 'webcatalog')"}:</th><td><input type="text" name="price" value="{$price}"></td>
            </tr>
            
            <tr style="background-color: transparent;">
                <th>{function="localize('Paid', 'webcatalog')"}:</th>
                <td>
                    <select name="ispaid">
                        <option value="0">{function="localize('No')"}</option>
                        <option value="1" {if="$ispaid"}selected{/if}>{function="localize('Yes')"}</option>
                    </select>
                </td>
            </tr>
            
            <tr style="background-color: transparent;">
                <th>{function="localize('Category', 'webcatalog')"}:</th>
                <td>
                    <select name="category">
                        {loop="$categories"}
                        <option value="{$key}" {if="$category_id == $key"}selected{/if}>{$value}</option>
                        {/loop}
                    </select>
                </td>
            </tr>
            
            <tr style="background-color: transparent;">
                <th>{function="localize('Script', 'webcatalog')"}:</th><td><input type="text" name="base_script" value="{$base_script}"></td>
            </tr>
        </tbody>
        
        <tfoot>
            <tr>
                <td colspan="2" style="padding-top: 35px;">
                    <input type="hidden" name="id" value="{$id}">
                    <input type="button" value="{function="localize('Cancel')"}" onclick="panthera.popup.close()" style="float: left; margin-left: 30px;">
                    <input type="submit" value="{function="localize('Save changes', 'webcatalog')"}" style="float: right; margin-right: 30px;">
                </td>
            </tr>
        </tfoot>
    </table>
</form>
</div>
