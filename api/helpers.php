<?php
function generateRequestID($conn, $table, $column, $prefix) {
    $year = date('y'); // e.g., 25
    $fullPrefix = $prefix . $year; // e.g., B25
    
    // Find the maximum ID with the current prefix
    // We assume the ID format is Prefix + Year + 6 digits (e.g., B25000001)
    // Length of prefix (e.g., B) + Year (2) = Length of fullPrefix
    // We want to extract the number part.
    // SUBSTRING(column, length(fullPrefix) + 1)
    
    $sql = "SELECT MAX(CAST(SUBSTRING($column, LENGTH(?) + 1) AS UNSIGNED)) as max_num 
            FROM $table 
            WHERE $column LIKE ?";
            
    $stmt = $conn->prepare($sql);
    $likePattern = $fullPrefix . '%';
    $stmt->execute([$fullPrefix, $likePattern]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    $nextNum = ($result['max_num'] ?? 0) + 1;
    
    // Pad with 0 to 6 digits
    return $fullPrefix . str_pad($nextNum, 6, '0', STR_PAD_LEFT);
}
