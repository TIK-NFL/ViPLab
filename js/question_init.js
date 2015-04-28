YAHOO.util.Event.onDOMReady(ilViPLabInitEditor,prefix = new String(""));

function ilViPLabInitEditor()
{
	$("#taForm").submit(
		function(event) {
			
			if(typeof ViPLab != 'undefined')
			{
				var solution = ViPLab.getSolution();
				var result = ViPLab.getResult();
				
				document.getElementById("vipsolution").value = solution.toString();
				document.getElementById("vipresult").value = result.toString();
			}
		}
	)
}
