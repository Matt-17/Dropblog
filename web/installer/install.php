<?php

class Installer {
    private $repoOwner = 'your-username';  // GitHub username
    private $repoName = 'your-repo';       // Repository name
    private $installDir;
    private $tempDir;
    private $githubApiUrl;
    private $installerPath;

    public function __construct() {
        $this->installDir = dirname(__FILE__);  // Directory where install.php is located
        $this->tempDir = $this->installDir . '/temp_install';
        $this->githubApiUrl = "https://api.github.com/repos/{$this->repoOwner}/{$this->repoName}/releases/latest";
        $this->installerPath = __FILE__;
    }

    public function install() {
        try {
            // Check if already installed
            if (file_exists($this->installDir . '/version.txt')) {
                throw new Exception("Application is already installed. Use the update script for updates.");
            }

            // Download latest release
            echo "Downloading latest release...\n";
            $zipPath = $this->downloadLatestRelease();
            if (!$zipPath) {
                throw new Exception("Failed to download the latest release");
            }

            // Extract and install
            echo "Installing application...\n";
            if ($this->extractAndInstall($zipPath)) {
                echo "Installation completed successfully!\n";
                echo "Version: " . $this->getInstalledVersion() . "\n";
                echo "\nIMPORTANT: Please update your web server configuration to point to the 'wwwroot' directory.\n";
                echo "For example, if your blog is at /html/blog, set the document root to /html/blog/wwwroot\n\n";
                
                // Remove installer after successful installation
                $this->removeInstaller();
            } else {
                throw new Exception("Failed to install the application");
            }
        } catch (Exception $e) {
            echo "Error: " . $e->getMessage() . "\n";
            return false;
        } finally {
            $this->cleanup();
        }
        return true;
    }

    private function removeInstaller() {
        echo "Removing installer...\n";
        if (file_exists($this->installerPath)) {
            unlink($this->installerPath);
            echo "Installer removed successfully.\n";
        }
    }

    private function downloadLatestRelease() {
        try {
            $response = $this->makeRequest($this->githubApiUrl);
            $latestRelease = json_decode($response, true);
            $zipUrl = $latestRelease['zipball_url'];

            if (!file_exists($this->tempDir)) {
                mkdir($this->tempDir, 0777, true);
            }

            $zipContent = $this->makeRequest($zipUrl);
            $zipPath = $this->tempDir . '/install.zip';
            file_put_contents($zipPath, $zipContent);

            return $zipPath;
        } catch (Exception $e) {
            error_log("Error downloading release: " . $e->getMessage());
            return null;
        }
    }

    private function extractAndInstall($zipPath) {
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

                // Copy all files to the installation directory
                $this->copyDirectory($extractedDir, $this->installDir);

                return true;
            }
            return false;
        } catch (Exception $e) {
            error_log("Error installing: " . $e->getMessage());
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
        curl_setopt($ch, CURLOPT_USERAGENT, 'PHP Installer');
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

    private function getInstalledVersion() {
        $versionFile = $this->installDir . '/version.txt';
        if (file_exists($versionFile)) {
            return trim(file_get_contents($versionFile));
        }
        return 'unknown';
    }
}

// Main execution
try {
    $installer = new Installer();
    $installer->install();
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
} 