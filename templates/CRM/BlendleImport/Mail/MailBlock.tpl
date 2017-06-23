<table width="640" cellspacing="0" cellpadding="2" border="1">
    <tr>
        <th>Article Title</th>
        <th>Sales Count</th>
        <th>Premium Reads</th>
        <th>Refunded Amount</th>
        <th>Vmoney Amount</th>
        <th>Revenue</th>
        <th>Campaign Costs</th>
    </tr>
  {foreach from=$data.activities key=aid item=activity}
    <tr>
        <td>{$activity.activity_article_title}</td>
        <td align="right">{$activity.activity_sales_count}</td>
        <td align="right">{$activity.activity_premium_reads}</td>
        <td align="right">{$activity.activity_refunded_amount}</td>
        <td align="right">{$activity.activity_vmoney_amount}</td>
        <td align="right">{$activity.activity_revenue}</td>
        <td align="right">{$activity.activity_fb_costs}</td>
    </tr>
   {foreachelse}
    <tr>
        <td colspan="6">{ts}No articles found!{/ts}</td>
    </tr>
    {/foreach}

    <tr>
        <td colspan="4"><strong>{ts}Total{/ts}</strong></td>
        <td colspan="2" align="right"><strong>&euro; {$data.total_formatted}</strong></td>
    </tr>
</table>