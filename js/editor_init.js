function ilViPLabInitEditor()
{
	// Add listener to programming language
	//document.getElementById("language").addEventListener("change", ilViPLabToggleEditor, false);
	
	$("#form_viplabquestion").submit(
		function(event) {

			if(typeof ViPLab != 'undefined')
			{
				var exercise = ViPLab.getLoadedExercise();
				var evaluation = ViPLab.getEvaluationCode();
			
				document.getElementById("vipexercise").value = exercise.toString();
				document.getElementById("vipevaluation").value = evaluation.toString();
			}
		}
	)
}
