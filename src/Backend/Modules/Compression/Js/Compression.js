/**
 * Interaction for the compression module
 *
 * @author Jesse Dobbelaere <jesse@dobbelaere-ae.be>
 */

// Stop the execution of the console
var $consoleFinished = false;

jsBackend.compression =
{
	// init, something like a constructor
	init: function () {
		jsBackend.compression.jsTree();
		jsBackend.compression.console();
	},

	/**
	 * Build the JsTree directory structure
	 */
	jsTree: function () {
		var $tree = $('#jstree-folders');

		// Init jstree
		$tree.jstree({
			"plugins": ["checkbox"]
		});

		// Listen for events
		$tree.on('changed.jstree', function (e, data) {
			var $selectedNodes = $tree.jstree(true).get_bottom_checked();

			var $allFolders = [];
			for (var i=0; i < $selectedNodes.length; i++) {
				var $path = $tree.jstree(true).get_node($selectedNodes[i]).data.path;
				$allFolders.push($path);
			}

			// Save the folders to html
			jsBackend.compression.save($allFolders);
		});

		// Check the checkboxes that have class "checked" on page load
		$.each($('#jstree-folders').jstree(true)._model.data, function(i, value) {
			$this = $(this);

			if ($this.prop("li_attr")) {
				var $listAttribute = $this[0].li_attr;
				if ($listAttribute.class) {
					$tree.jstree(true).select_node(i);
				}
			}
		});
	},

	/**
	 * Save all folders to a hidden field so we can process it in PHP.
	 */
	save: function($folders)
	{
		// remove all folders before reading them
		$('input.folders').remove();

		// Add hidden folders to parse in PHP
		$.each($folders, function(index, folder)
		{
			// create a element based on this hidden field
			$folderInput = $('input#dummyFolders').clone();
			$folderInput.attr('name', 'folders[]');
			$folderInput.attr('id', '');
			$folderInput.addClass('folders');
			$folderInput.val(folder);

			$('.jstree-wrapper').append($folderInput);
		});

	},

	/**
	 * Execute the compression script with output in a console window
	 */
	console: function() {

		$('#console-btn').click(function() {
			$consoleFinished = false;

			//Erase file first
			$.ajax({
				data: {
					fork: { module: 'Compression', action: 'ConsoleErase' }
				},
				success: function (result) {
					if (result.code === 200) {
						console.log("Erased");
						setTimeout(jsBackend.compression.consoleCompressImages(), 100);

						setTimeout(function() {
							setTimeout(jsBackend.compression.consolePeriodicRead(), 100);
						}, 1500);
					}
				}
			});

		});
	},

	/**
	 * Periodically read the file to the console window
	 */
	consolePeriodicRead: function() {
		(function periodicOutput() {
			$.ajax({
				url: '/src/Backend/Cache/Compression/output.log',
				type: 'GET',
				dataType: 'text',
				success: function (data) {
					$("#console").html(data);
					console.log("File read");

					if(!$consoleFinished) {
						setTimeout(periodicOutput, 500);
					}
				},
				error: function (jqXHR, textStatus, errorThrown) {
					console.log("Can't read file");
					if(!$consoleFinished) {
						setTimeout(periodicOutput, 500);
					}
				}
			});
		})();
	},

	/**
	 * Start the compression of the images
	 */
	consoleCompressImages: function() {
		$.ajax({
			data: {
				fork: { module: 'Compression', action: 'StartCompression' }
			},
			success: function (result) {
				if (result.code === 200) {
					console.log("Finished image compression");
					$consoleFinished = true;
				}
			},
			error: function (request, status, error) {
				console.log('error');
				console.log(error);
			},
			timeout: 120000 // 2minutes
		});
	}

};

$(jsBackend.compression.init);
