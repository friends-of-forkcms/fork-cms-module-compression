/**
 * Interaction for the compression module
 *
 * @author Jesse Dobbelaere <jesse@dobbelaere-ae.be>
 */
jsBackend.compression =
{
	// init, something like a constructor
	init: function () {
		jsBackend.compression.jsTree();
	},


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
				var $path = $tree.jstree(true).get_path($selectedNodes[i], '/');
				console.log($path);
				$allFolders.push($path);
			}

			// Save the folders to html
			jsBackend.compression.save($allFolders);
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
			//var folderId = $(this).data('id') !== undefined ? $(this).data('id') : '';
			$folderInput.val(folder);

			$('.jstree-wrapper').append($folderInput);
		});

	}

};

$(jsBackend.compression.init);
