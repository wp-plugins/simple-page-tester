jQuery(document).ready(function(){
	jQuery('#master_weight').change(function() {
		var master_weight = parseInt(jQuery('#master_weight').val());
		var slave_weight = parseInt(jQuery('#slave_weight').val());
		
		jQuery('#slave_weight').val(100 - master_weight)
		
	});
	
	jQuery('#slave_weight').change(function() {
		var master_weight = parseInt(jQuery('#master_weight').val());
		var slave_weight = parseInt(jQuery('#slave_weight').val());
		
		jQuery('#master_weight').val(100 - slave_weight)
		
	});
	
	jQuery('.spt_declare').click(function() {
		var winner_id = jQuery(this).attr('id');
		jQuery(this).next('.winner_loader').show();
		
		jQuery.post(
			ajaxurl,
			{
				action: 'sptDeclareWinner',
				winner_id: winner_id
			},
			function(resultWinnerId) {
				jQuery('.winner_loader').hide();
				self.parent.location.href = sptAdminUrl + 'post.php?post=' + resultWinnerId + '&action=edit&message=1';
			}
		);
		
	});

	jQuery('#sptResetAllStats').click(function() {
		var spt_id = jQuery('input#post_ID').val();
		
		jQuery(this).addClass('button-disabled');
		jQuery('#sptResetLoader').show();

		jQuery.post(
			ajaxurl,
			{
				action: 'sptResetAllStats',
				spt_id: spt_id
			},
			function(result) {
				jQuery('#sptResetLoader').hide();
				window.location.reload(false);
			}
		);

	});
});

function sptDrawChart(chart_data, chart_id_name) {
	if (typeof chart_data === 'undefined')
		chart_data = Array();

	var data = new google.visualization.DataTable();
	data.addColumn("string", "Date");

	data.addColumn("number", "Visits (Master)");
	
	if (chart_data[0].length == 5)
		data.addColumn("number", "Conversions (Master)");

	data.addColumn("number", "Visits (Variation)");

	if (chart_data[0].length == 5)
		data.addColumn("number", "Conversions (Variation)");
	
	data.addRows(chart_data);
	
	var options = {
		width: "100%",
		height: "100%",
		title: "",
		backgroundColor: {
			fill: "transparent",
			stroke: "#eeeeee"
		},
		chartArea: {
			width: "100%", 
			height: "75%",
			top: 0
		},
		legend: {
			position: "bottom",
			alignment: "center"
		},
		hAxis: {
			textPosition: "out"
		},
		vAxis: {
			textPosition: "in",
			format: "#,###",
			minValue: 1
		}
	};
	
	var chart = new google.visualization.AreaChart(document.getElementById(chart_id_name));
	chart.draw(data, options);
	
}

