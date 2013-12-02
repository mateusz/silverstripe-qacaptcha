<% if RandomQuestion %>
	<div id="$Name" class="field $Type $extraClass">
		<div class="middleColumn">
			<h3>Please answer this question:</h3>
			<% control RandomQuestion %>
				<p>$Question</p>
				<p style='display: none'><a class="qacaptcha-otherquestion" href='$Top.Link/otherquestion'>Show another question</a></p>
				<input type="text" id="{$Top.id}" value="{$Top.attrValue}" name="{$Top.Name}" class="text"/>
				<input type="hidden" value="$ID" name="QACaptchaQuestionID" id="{$Top.FormName}_QACaptchaQuestionID" class="hidden"/>
			<% end_control %>
		</div>
		<% if Message %>
			<span class="message $MessageType">$Message</span>
		<% end_if %>
	</div>
<% end_if %>
