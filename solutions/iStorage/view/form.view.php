<div id="solution_iStorage">
	<!-- The global progress bar -->
	<div id="progress" class="progress">
		<div class="progress-bar progress-bar-success"></div>
	</div>
	<!-- The fileinput-button span is used to style the file input field as button -->
	<span class="btn btn-success fileinput-button">
        <i class="glyphicon glyphicon-plus"></i>
        <span>Add files...</span>
        <!-- The file input field used as target for the file upload widget -->
        <input id="fileupload" type="file" name="files[]" multiple>
    </span>
	<span class="btn btn-success fileinput-button">
        <i class="glyphicon glyphicon-floppy-saved"></i>
        <span>Save</span>
        <!-- The file input field used as target for the file upload widget -->
        <input id="fileSave" type="submit">
    </span>
	<br>
	<br>
	<!-- The container for the uploaded files -->
	<div id="files" class="files"></div>
</div>
