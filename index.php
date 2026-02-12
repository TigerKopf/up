<?php
// --- KONFIGURATION VIA UMGEBUNGSVARIABLEN ---
// Coolify zieht diese Werte aus dem Reiter "Environment Variables"
$webhook_url = getenv('DISCORD_WEBHOOK_URL'); 
$max_size_mb = getenv('MAX_UPLOAD_SIZE') ?: 25; // Standardmäßig 25MB, falls nichts gesetzt
$max_file_size = $max_size_mb * 1024 * 1024; 
// --------------------------------------------

$message = "";
$status = "";

// Validierung: Prüfen ob Webhook vorhanden ist
if (!$webhook_url) {
    $message = "Konfigurationsfehler: DISCORD_WEBHOOK_URL fehlt!";
    $status = "error";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['files']) && $webhook_url) {
    $files = $_FILES['files'];
    
    foreach ($files['name'] as $key => $name) {
        if ($files['error'][$key] === 0) {
            if ($files['size'][$key] <= $max_file_size) {
                
                $file_path = $files['tmp_name'][$key];
                $file_mime = $files['type'][$key];
                $file_name = $files['name'][$key];

                $curl = curl_init($webhook_url);
                $cfile = new CURLFile($file_path, $file_mime, $file_name);
                
                $data = [
                    'file' => $cfile,
                    'content' => "Neuer Upload von Webseite: **$file_name**"
                ];

                curl_setopt($curl, CURLOPT_POST, true);
                curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
                curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
                
                $response = curl_exec($curl);
                $http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
                curl_close($curl);

                if ($http_code >= 200 && $http_code < 300) {
                    $message = "Erfolgreich an Discord gesendet!";
                    $status = "success";
                } else {
                    $message = "Discord Fehler: Code $http_code";
                    $status = "error";
                }
            } else {
                $message = "Datei zu groß! Limit: " . $max_size_mb . "MB";
                $status = "error";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Discord Uploader</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-900 text-white min-h-screen flex items-center justify-center p-6">

    <div class="max-w-md w-full bg-slate-800 rounded-2xl shadow-2xl p-8 border border-slate-700">
        <h2 class="text-2xl font-bold mb-6 text-center bg-gradient-to-r from-blue-400 to-purple-500 bg-clip-text text-transparent">
            Discord Media Uploader
        </h2>

        <?php if ($message): ?>
            <div class="mb-4 p-3 rounded text-sm text-center <?php echo $status === 'success' ? 'bg-green-500/20 text-green-400' : 'bg-red-500/20 text-red-400'; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <form action="" method="POST" enctype="multipart/form-data" class="space-y-6">
            <div class="border-2 border-dashed border-slate-600 rounded-xl p-8 text-center hover:border-blue-500 transition-colors cursor-pointer relative">
                <input type="file" name="files[]" id="fileInput" multiple class="absolute inset-0 w-full h-full opacity-0 cursor-pointer" onchange="updateList()">
                <div id="uploadPlaceholder">
                    <svg class="w-12 h-12 mx-auto text-slate-500 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path></svg>
                    <p class="text-slate-400">Dateien auswählen</p>
                    <p class="text-xs text-slate-500 mt-2">Limit: <?php echo $max_size_mb; ?>MB pro Datei</p>
                </div>
                <div id="fileList" class="text-sm text-blue-400 font-medium mt-2"></div>
            </div>

            <button type="submit" class="w-full bg-blue-600 hover:bg-blue-500 text-white font-bold py-3 rounded-xl transition-all shadow-lg shadow-blue-600/20">
                An Discord senden
            </button>
        </form>
    </div>

    <script>
        function updateList() {
            const input = document.getElementById('fileInput');
            const list = document.getElementById('fileList');
            const placeholder = document.getElementById('uploadPlaceholder');
            if (input.files.length > 0) {
                placeholder.classList.add('hidden');
                list.innerHTML = input.files.length + " Datei(en) ausgewählt";
            }
        }
    </script>
</body>
</html>
