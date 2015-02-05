{include:{$BACKEND_CORE_PATH}/Layout/Templates/Head.tpl}
{include:{$BACKEND_CORE_PATH}/Layout/Templates/StructureStartModule.tpl}

<div class="pageTitle">
	<h2>{$lblModuleSettings|ucfirst}: {$lblCompression}</h2>
</div>

{option:Wizard}
	<div class="generalMessage infoMessage content">
		<p><strong>{$msgConfigurationError}</strong></p>
		<ul class="pb0">
			{option:NoApiKey}<li>{$errNoApiKey}</li>{/option:NoApiKey}
			{option:NoFoldersSet}<li>{$errNoFoldersSet}</li>{/option:NoFoldersSet}
		</ul>
	</div>
{/option:Wizard}

{option:Wizard}
<div class="box">
	<div class="heading">
		<h3>{$lblTinyPNGLink|ucfirst}</h3>
	</div>

	<div class="options">
			{option:NoApiKey}
				{form:apiKey}
					<p>{$msgLinkTinyPNGAccount}</p>

					<div class="inputList">
						<label for="key">{$lblApiKey|ucfirst}</label>
						{$txtKey} {$txtKeyError}
					</div>

					<div class="buttonHolder">
						<input id="submitForm" class="inputButton button mainButton" type="submit" name="submitForm" value="{$msgAuthenticateAtTinyPNG}" />
					</div>

				{/form:apiKey}
			{/option:NoApiKey}


	</div>
</div>
{/option:Wizard}

{option:EverythingIsPresent}
{form:compressionSettings}
	<div class="box">
		<div class="heading">
			<h3>{$lblSettings|ucfirst}</h3>
		</div>

		<div class="options jstree-wrapper">
			<div id="jstree-folders">
				{$directoryTree}
			</div>
			{$hidDummyFolders}

		</div>
	</div>

	<div class="fullwidthOptions">
		<div class="buttonHolderLeft">
			<a href="{$var|geturl:'settings'}&amp;remove=api_key" data-message-id="confirmDeleteAccountLink" class="askConfirmation button inputButton"><span>{$msgRemoveAccountLink}</span></a>
			{*{option:showAnalyticsIndex}<a href="{$var|geturl:'index'}" class="mainButton button"><span>{$lblViewStatistics|ucfirst}</span></a>{/option:showAnalyticsIndex}*}
		</div>
		<div class="buttonHolderRight">
			<input id="save" class="inputButton button mainButton" type="submit" name="save" value="{$lblSave|ucfirst}" />
		</div>
	</div>

	<div id="confirmDeleteAccountLink" title="{$lblDelete|ucfirst}?" style="display: none;">
		<p>
			{$msgConfirmDeleteLinkAccount}
		</p>
	</div>
{/form:compressionSettings}
{/option:EverythingIsPresent}

{include:{$BACKEND_CORE_PATH}/Layout/Templates/StructureEndModule.tpl}
{include:{$BACKEND_CORE_PATH}/Layout/Templates/Footer.tpl}
