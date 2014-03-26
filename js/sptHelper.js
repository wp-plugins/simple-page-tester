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
});

function sptDrawChart(row_data, chart_id_name) {
	if (isNaN(row_data.length))
		row_data = Array();

	var data = new google.visualization.DataTable();
	data.addColumn("string", "Date");
	data.addColumn("number", "Visits");
	
	if (row_data[0].length == 3)
		data.addColumn("number", "Conversions");
	
	data.addRows(row_data);
	
	var options = {
		width: "100%",
		height: "100%",
		title: "",
		backgroundColor: "transparent",
		chartArea: {
			width: "100%", 
			height: "100%"
		},
		legend: {
			position: "none"
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
	
	var clickTotal = 0;
	for (var i = 0; i < row_data.length; i++) {
		clickTotal += row_data[i][1];
	}
}