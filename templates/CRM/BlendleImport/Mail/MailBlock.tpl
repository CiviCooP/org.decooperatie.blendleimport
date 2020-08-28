<table width="640" cellspacing="0" cellpadding="2" border="1">
    <tr>
        <th>Author</th>
        <th>Article Title</th>
        <th>Read Count</th>
        <th>Revenue</th>
        <th>Donations</th>
    </tr>
  {foreach from=$data.activities key=aid item=activity}
    <tr>
        <td>{$activity.activity_author}</td>
        <td>{$activity.activity_article_title}</td>
        <td align="right">{if $activity.activity_sales_count > 0 }{$activity.activity_sales_count}{else}-{/if}</td>
        <td align="right">{$activity.activity_revenue}</td>
        <td align="right">{$activity.activity_vmoney_amount}</td>
    </tr>
   {foreachelse}
    <tr>
        <td colspan="8">{ts}No articles found!{/ts}</td>
    </tr>
    {/foreach}

    <tr>
        <td colspan="3"><strong>{ts}Total{/ts}</strong></td>
        <td colspan="2" align="right"><strong>&euro; {$data.total_formatted}</strong></td>
    </tr>
</table>
