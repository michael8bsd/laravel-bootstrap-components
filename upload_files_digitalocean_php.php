<?php
require 'vendor/autoload.php';

use Aws\S3\S3Client;
use Aws\Exception\AwsException;

// DigitalOcean Spaces credentials and details
$key = 'XXXXXXXXXXXXXXX';
$secret = 'XXXXXXXXXXXXXXXXXX';
$spaceName = 'yor-space-name';
$region = 'ams3'; // e.g., 'nyc3'
$host = $spaceName . '.' . $region . '.digitaloceanspaces.com';

// Create an S3Client
$client = new S3Client([
    'version' => 'latest',
    'region'  => $region,
    'endpoint' => 'https://' . $host,
    'credentials' => [
        'key'    => $key,
        'secret' => $secret,
    ],
    'bucket_endpoint' => true,
    'http'    => [
        'verify' => false // You can enable SSL verification if you have the certificates
    ]
]);

// HTML form for file upload
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>File Upload</title>
</head>
<body>
    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" method="post" enctype="multipart/form-data">
        <input type="file" name="fileToUpload" id="fileToUpload">
        <input type="submit" value="Upload File" name="submit">
    </form>
</body>
</html>

<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        // File handling
        $fileTmpPath = $_FILES['fileToUpload']['tmp_name'];
        $fileName = $_FILES['fileToUpload']['name'];
        $fileResource = fopen($fileTmpPath, 'rb');

        // Uploading to DigitalOcean Spaces
        $result = $client->putObject([
            'Bucket' => $spaceName,
            'Key'    => $fileName,
            'Body'   => $fileResource,
            'ACL'    => 'public-read' // or 'private'
        ]);

        echo "File uploaded successfully. File URL: " . $result['ObjectURL'];
    } catch (AwsException $e) {
        // Output error message if fails
        echo "Error uploading file: " . $e->getMessage();
    }
}
?>
