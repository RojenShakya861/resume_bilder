function getJobRecommendations($skills) {
    $api_url = 'http://localhost:5000/predict';
    
    // Prepare the data
    $data = json_encode(['skills' => $skills]);
    
    // Initialize cURL
    $ch = curl_init($api_url);
    
    // Set cURL options
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Content-Length: ' . strlen($data)
    ]);
    
    // Execute the request
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    
    // Check for errors
    if (curl_errno($ch)) {
        error_log('cURL Error: ' . curl_error($ch));
        return null;
    }
    
    curl_close($ch);
    
    // Process the response
    if ($http_code === 200) {
        $result = json_decode($response, true);
        if ($result && $result['status'] === 'success') {
            return [
                'job_title' => $result['job_title'],
                'confidence' => $result['confidence']
            ];
        }
    }
    
    return null;
} 