<div class="crm-content-block">
    <div class="help">
        {capture assign=crmURL}{crmURL p="civicrm/a/#blendleimport?action=create"}{/capture}
        {ts 1=$crmURL}This page allows you to import and process data from CSV files generated by Blendle. You can
            <a href="%1">start a new import</a>, or view and continue previous imports below.{/ts}
    </div>

    {if !($action eq 1 and $action eq 2)}
        <div class="crm-submit-buttons" style="margin-bottom:15px;">
          {if $reportInstanceUrl}
              <div class="action-link" style="float:right;">
                {crmButton p=$reportInstanceUrl id="revenueReport" icon="list-alt"}{ts}View Report{/ts}{/crmButton}
              </div>
          {/if}

            <div class="action-link">
                {crmButton p="civicrm/a/#blendleimport?action=create" id="newImportJob" icon="plus-circle"}{ts}New Import{/ts}{/crmButton}
            </div>
        </div>
    {/if}

    {include file="CRM/common/jsortable.tpl"}
    <div id="merge_tag_status"></div>
    <div id="cat">
        {strip}
            <table id="options" class="display">
                <thead>
                <tr>
                    <th>{ts}ID{/ts}</th>
                    <th>{ts}Name{/ts}</th>
                    <th>{ts}Publication{/ts}</th>
                    <th>{ts}Created{/ts}</th>
                    <th>{ts}Status{/ts}</th>
                    <th></th>
                </tr>
                </thead>
                {foreach from=$rows item=row key=id }
                    <tr class="{cycle values="odd-row,even-row"} crm-importjob crm-entity"
                        id="importjob-{$row->id}">
                        <td class="crm-importjob-id">{$row->id}</td>
                        <td class="crm-importjob-name">{$row->name}</td>
                        <td class="crm-importjob-publication">{$row->publication}</td>
                        <td class="crm-importjob-created">{$row->created_date|crmDate:'%d-%m-%Y'}</td>
                        <td class="crm-importjob-status">{$row->getStatusDescription()}</td>
                        <td class="crm-importjob-actions">
                            <a class="action-item crm-hover-button" title="{ts}Continue{/ts}" href="{crmURL p="civicrm/a/#blendleimport?action=update&id=`$row->id`" }">{ts}Continue{/ts}</a>
                            <a href="#" class="action-item crm-hover-button crm-importjob-delete" title="{ts}Delete{/ts}" data-delete-url="{crmURL p="civicrm/blendleimport/delete?id=`$row->id`"}" >{ts}Delete{/ts}</a>
                        </td>
                    </tr>
                {/foreach}
            </table>
        {/strip}
    </div>

</div>

{literal}
<script type="text/javascript">
    jQuery(function($) {
        $('.crm-importjob-delete').click(function(ev) {
            ev.preventDefault();
            var that = $(this);

            CRM.confirm({
                    title: ts('Confirm delete'),
                    message: ts("Are you sure you want to delete this import job?") + "<br>\n" + ts("(This will NOT delete imported activities and payments. You will no longer be able to resume this import job.)")
                }).on('crmConfirm:yes', function() {
                    location.href = that.attr('data-delete-url');
                });
        });
    });
</script>
{/literal}