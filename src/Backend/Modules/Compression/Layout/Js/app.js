'use strict';

// React imported globally via Unpkg in the twig files
const { useState, useEffect } = React;

/**
 * Render a folder tree structure using React
 */
function CompressionSettingsTree({tree, selectedFolders}) {
  const [nodes, setNodes] = useState(tree);
  const [checked, setChecked] = useState(selectedFolders);
  const [expanded, setExpanded] = useState([]);

  // Add the count to our labels on initial mount of the component.
  useEffect(() => {
    setNodes(appendCountToLabel(tree));
  }, []);

  function appendCountToLabel(nodes) {
    return nodes.map(node => {
      node.label += ` (${node.count})`;
      if (node.children) {
        node.children = appendCountToLabel(node.children);
      }
      return node;
    });
  }

  return (
    <ReactCheckboxTree
      nodes={nodes}
      checked={checked}
      expanded={expanded}
      onCheck={(checked) => setChecked(checked)}
      onExpand={expanded => setExpanded(expanded)}
      name={'compression_settings[folders]'}
    />
  );
}
ReactDOM.render(<CompressionSettingsTree {...window.COMPRESSION_APP} />, document.getElementById('compression-app'));

/**
 * Start streaming SSE events when we click the Execute button
 */
document.getElementById('js-btn-console').addEventListener('click', function(event) {
  event.preventDefault();
  const consoleTxtArea = document.getElementById('js-compression-console');

  function logMessage(message) {
    consoleTxtArea.innerHTML += moment().format('hh:mm:ss') + " ";
    consoleTxtArea.innerHTML += message;
    consoleTxtArea.innerHTML += "\n";
    consoleTxtArea.scrollTop += 20;
  }

  // Erase console first
  consoleTxtArea.innerHTML = '';

  // Write immediately some feedback
  logMessage("Compression is starting...");

  // Establish a "Server-Sent Events" stream with the PHP code and log responses to the console
  if (!!window.EventSource) {
    const eventSource = new EventSource("compress_images");

    // Process and send events to the console panel
    eventSource.addEventListener("open", (e) => logMessage("Started the compression engines..."));
    eventSource.addEventListener("compression-event", (event) => {
      // Gracefully shut down the stream when we reach an END-OF-STREAM delimiter.
      if (event.data.endsWith("END-OF-STREAM")) {
        logMessage("...Done!");
        eventSource.close();
        return;
      }

      logMessage(event.data);
    });
    eventSource.addEventListener("error", (event) => {
      logMessage("Server Error occurred.");
      eventSource.close();

      if (event.readyState === EventSource.CLOSED) {
        logMessage("Error: Connection closed");
      }
    });

    // Safely close the stream when page exits.
    window.onunload = () => eventSource.close();
  }
});
