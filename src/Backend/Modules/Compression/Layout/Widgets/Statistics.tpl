<div class="box" id="widgetCompressionStatistics">
	<div class="heading">
		<h3>{$lblCompression|ucfirst}: {$lblStatistics|ucfirst}</h3>
	</div>

	<div class="options content">

        {option:statistics.statistics_enable}
		<table class="infoGrid m0">
			<tr>
				<th>{$lblTotalCompressed|ucfirst}:</th>
				<td>
					{$statistics.total_compressed} {$lblImages}
				</td>
			</tr>
            <tr>
                <th>{$lblSavedBytes|ucfirst}:</th>
                <td>
                    {$statistics.saved_bytes}
                </td>
            </tr>
            <tr>
                <th>{$lblSavedPercentage|ucfirst}:</th>
                <td>
                    {$msgYouSavedPercentageImages|sprintf:{$statistics.saved_percentage}}
                </td>
            </tr>
		</table>
        {/option:statistics.statistics_enable}

        {option:!statistics.statistics_enable}
            <p>
                {$lblNoStatistics|ucfirst}
            </p>
        {/option:!statistics.statistics_enable}
	</div>
</div>