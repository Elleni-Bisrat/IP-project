<?php
$conn = new mysqli("localhost", "root", "", "resources");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$rootFolder = __DIR__ . "/Bookscollection/handouts/";

$yearMap = [
    "firstyear" => "1",
    "secondyear" => "2",
    "thirdyear" => "3",
    "fourthyear" => "4",
    "fifthyear" => "5",
    "1styear" => "1",
    "2ndyear" => "2",
    "3rdyear" => "3",
    "4thyear" => "4",
    "5thyear" => "5",
    "year1" => "1",
    "year2" => "2",
    "year3" => "3",
    "year4" => "4",
    "year5" => "5"
];

function getAllPDFFiles($dir) {
    $pdfFiles = [];
    $items = scandir($dir);
    
    foreach ($items as $item) {
        if ($item === "." || $item === "..") continue;
        $path = $dir . DIRECTORY_SEPARATOR . $item;
        
        if (is_dir($path)) {
            $pdfFiles = array_merge($pdfFiles, getAllPDFFiles($path));
        } elseif (strtolower(pathinfo($path, PATHINFO_EXTENSION)) === "pdf") {
            $pdfFiles[] = $path;
        }
    }
    return $pdfFiles;
}

function extractChapterNumber($filename) {
    // Try common chapter patterns in the filename
    if (preg_match('/Ch[-\s]*(\d+)/i', $filename, $matches)) {
        return (int)$matches[1];
    }
    if (preg_match('/Chapter[-\s]*(\d+)/i', $filename, $matches)) {
        return (int)$matches[1];
    }
    return null; 
}

$allPDFs = getAllPDFFiles($rootFolder);

$stmt = $conn->prepare("INSERT INTO handouts_table (file_name, file_path, year, course_name, chapter, file_size) VALUES (?, ?, ?, ?, ?, ?)");
$stmt->bind_param("ssssid", $file_name, $file_path, $year, $course_name, $chapter, $file_size_MB);

foreach ($allPDFs as $file_path) {
    $file_name = basename($file_path);
    $file_size_MB = round(filesize($file_path) / (1024 * 1024), 2);
    $chapter = extractChapterNumber($file_name);
    
    $normalizedPath = str_replace('\\', '/', $file_path);
    $normalizedPath = preg_replace('#/+#', '/', $normalizedPath);
    
    $relativePath = str_replace($rootFolder, '', $normalizedPath);
    $pathParts = explode('/', $relativePath);
    
    $year = null;
    $course_name = null;
    
    foreach ($pathParts as $i => $part) {
        $lowerPart = strtolower(trim($part));
        
        if (isset($yearMap[$lowerPart])) {
            $year = $yearMap[$lowerPart];
            
            if (isset($pathParts[$i+1])) {
                $course_name = $pathParts[$i+1];
                $course_name = ucwords(str_replace(['_', '-', '%20'], ' ', $course_name));
                $course_name = preg_replace('/[^a-zA-Z0-9\s]/', '', $course_name);
            }
            break;
        }
    }
    
    if ($year === null) {
        foreach ($pathParts as $part) {
            if (preg_match('/y(\d)/i', $part, $matches)) {
                $year = $matches[1];
                break;
            }
        }
    }
    
    // Check for duplicates
    $check = $conn->prepare("SELECT id FROM handouts_table WHERE file_name = ?");
    $check->bind_param("s", $file_name);
    $check->execute();
    $check->store_result();
    
    if ($check->num_rows > 0) {
        echo "Skipping duplicate: $file_name\n";
        continue;
    }
    
    // Insert into database
    if ($stmt->execute()) {
        echo "Inserted: $file_name (Year: " . ($year ?? 'NULL') . ", Course: " . ($course_name ?? 'NULL') . ", Chapter: " . ($chapter ?? 'NULL') . ")\n";
    } else {
        echo "Error inserting $file_name: " . $stmt->error . "\n";
    }
}

$stmt->close();
$conn->close();
?>