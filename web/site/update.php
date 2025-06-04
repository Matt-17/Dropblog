<?php

class UpdateChecker {
    private $repoOwner = 'your-username';  // GitHub username
    private $repoName = 'your-repo';       // Repository name
    private $currentVersion;
    private $githubApiUrl;
    private $tempDir;
    private $baseDir;

    public function __construct() {
        $this->baseDir = dirname(__DIR__);  // Go up to the root directory
        $this->tempDir = $this->baseDir . '/temp_update';
        $this->githubApiUrl = "https://api.github.com/repos/{$this->repoOwner}/{$this->repoName}/releases/latest";
        $this->currentVersion = $this->getCurrentVersion();
    }

    public function checkForUpdates() {
        try {
            $response = $this->makeRequest($this->githubApiUrl);
            $latestRelease = json_decode($response, true);
            $latestVersion = ltrim($latestRelease['tag_name'], 'v');

            echo "Current version: " . $this->currentVersion . "\n";
            echo "Latest version: " . $latestVersion . "\n";

            return $this->compareVersions($latestVersion, $this->currentVersion);
        } catch (Exception $e) {
            echo "Error checking for updates: " . $e->getMessage() . "\n";
            return false;
        }
    }

    public function update() {
        try {
            if (!$this->checkForUpdates()) {
                echo "No updates available.\n";
                return true;
            }

            echo "Update available! Downloading...\n";
            $zipPath = $this->downloadUpdate();
            if (!$zipPath) {
                throw new Exception("Failed to download the update");
            }

            echo "Installing update...\n";
            if ($this->installUpdate($zipPath)) {
                echo "Update installed successfully!\n";
                echo "New version: " . $this->getCurrentVersion() . "\n";
                return true;
            } else {
                throw new Exception("Failed to install the update");
            }
        } catch (Exception $e) {
            echo "Error: " . $e->getMessage() . "\n";
            return false;
        } finally {
            $this->cleanup();
        }
    }

    private function getCurrentVersion() {
        $versionFile = $this->baseDir . '/version.txt';
        if (file_exists($versionFile)) {
            return trim(file_get_contents($versionFile));
        }
        return '0.0.0';
    }

    private function compareVersions($version1, $version2) {
        $v1Parts = array_map('intval', explode('.', $version1));
        $v2Parts = array_map('intval', explode('.', $version2));

        $maxLength = max(count($v1Parts), count($v2Parts));

        for ($i = 0; $i < $maxLength; $i++) {
            $v1 = isset($v1Parts[$i]) ? $v1Parts[$i] : 0;
            $v2 = isset($v2Parts[$i]) ? $v2Parts[$i] : 0;

            if ($v1 > $v2) {
                return true;
            } elseif ($v1 < $v2) {
                return false;
            }
        }
        return false;
    }

    private function downloadUpdate() {
        try {
            $response = $this->makeRequest($this->githubApiUrl);
            $latestRelease = json_decode($response, true);
            $zipUrl = $latestRelease['zipball_url'];

            if (!file_exists($this->tempDir)) {
                mkdir($this->tempDir, 0777, true);
            }

            $zipContent = $this->makeRequest($zipUrl);
            $zipPath = $this->tempDir . '/update.zip';
            file_put_contents($zipPath, $zipContent);

            return $zipPath;
        } catch (Exception $e) {
            error_log("Error downloading update: " . $e->getMessage());
            return null;
        }
    }

    private function installUpdate($zipPath) {
        try {
            $zip = new ZipArchive;
            if ($zip->open($zipPath) === TRUE) {
                $zip->extractTo($this->tempDir);
                $zip->close();

                $dirs = glob($this->tempDir . '/*', GLOB_ONLYDIR);
                if (empty($dirs)) {
                    throw new Exception("No directory found in zip file");
                }
                $extractedDir = $dirs[0];

                // Copy files to their proper locations
                $this->copyDirectory($extractedDir . '/src', $this->baseDir . '/src');
                $this->copyDirectory($extractedDir . '/web', $this->baseDir . '/web');
                
                // Copy only public files to wwwroot
                if (is_dir($extractedDir . '/web/wwwroot')) {
                    $this->copyDirectory($extractedDir . '/web/wwwroot', $this->baseDir . '/web/wwwroot');
                }

                // Update version file
                if (file_exists($extractedDir . '/version.txt')) {
                    copy($extractedDir . '/version.txt', $this->baseDir . '/version.txt');
                }

                return true;
            }
            return false;
        } catch (Exception $e) {
            error_log("Error installing update: " . $e->getMessage());
            return false;
        }
    }

    private function copyDirectory($source, $destination) {
        if (!is_dir($destination)) {
            mkdir($destination, 0777, true);
        }

        $dir = opendir($source);
        while (($file = readdir($dir)) !== false) {
            if ($file != '.' && $file != '..') {
                $srcFile = $source . '/' . $file;
                $destFile = $destination . '/' . $file;

                if (is_dir($srcFile)) {
                    $this->copyDirectory($srcFile, $destFile);
                } else {
                    copy($srcFile, $destFile);
                }
            }
        }
        closedir($dir);
    }

    private function cleanup() {
        if (file_exists($this->tempDir)) {
            $this->removeDirectory($this->tempDir);
        }
    }

    private function removeDirectory($dir) {
        if (!file_exists($dir)) {
            return;
        }

        $files = array_diff(scandir($dir), array('.', '..'));
        foreach ($files as $file) {
            $path = $dir . '/' . $file;
            is_dir($path) ? $this->removeDirectory($path) : unlink($path);
        }
        rmdir($dir);
    }

    private function makeRequest($url) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERAGENT, 'PHP Update Checker');
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        
        $response = curl_exec($ch);
        
        if (curl_errno($ch)) {
            throw new Exception(curl_error($ch));
        }
        
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if ($httpCode !== 200) {
            throw new Exception("HTTP request failed with code: " . $httpCode);
        }
        
        curl_close($ch);
        return $response;
    }
}

// Main execution
$action = isset($argv[1]) ? $argv[1] : 'check';

try {
    $updater = new UpdateChecker();
    
    switch ($action) {
        case 'update':
            $updater->update();
            break;
            
        case 'check':
        default:
            if ($updater->checkForUpdates()) {
                echo "Update available! Run 'php update.php update' to install.\n";
            } else {
                echo "No updates available.\n";
            }
            break;
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
} 