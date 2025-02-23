// sendSseMessage.js
// This is responsible for sending data to the server-side script (sse.php).
// V2 Updated: 2024-06-02

function sendDataToServer(data) {
  const url = 'sse.php';

  fetch(url, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json'
    },
    body: JSON.stringify(data)
  })
    .then(response => {
      if (!response.ok) {
        throw new Error(`HTTP error ${response.status}`);
      }
      console.log('SSE message sent successfully');
    })
    .catch(error => {
      console.error('Error sending SSE message:', error);
    });
}