<?php

class Installer {
    private const REPO_OWNER = 'Matt-17';
    private const REPO_NAME  = 'Dropblog';

    private string $installDir;
    private string $tempDir;
    private string $githubApiUrl;

    public function __construct() {
        $this->installDir = dirname(__FILE__);
        $this->tempDir = $this->installDir . '/temp_install';
        $this->githubApiUrl = "https://api.github.com/repos/" . self::REPO_OWNER . "/" . self::REPO_NAME . "/releases/latest";
    }

    public function install(): bool {
        try {
            if (file_exists($this->installDir . '/version.txt')) {
                throw new Exception("Application is already installed.");
            }

            echo "Downloading install.zip...\n";
            $zipPath = $this->downloadReleaseAsset('install.zip');
            if (!$zipPath) {
                throw new Exception("Download failed.");
            }

            echo "Installing...\n";
            $this->extractAndInstall($zipPath);
            echo "Done. Check wwwroot/, and delete this file.\n";     
            $this->copyEnvIfNeeded();
            $this->showReadmeHint();
            $this->selfDestruct();

        } catch (Exception $e) {
            echo "Error: " . $e->getMessage() . "\n";
            return false;
        } finally {
            $this->cleanup();
        }

        return true;
    }

    private function downloadReleaseAsset(string $assetName): ?string {
        $response = $this->makeRequest($this->githubApiUrl);
        $release = json_decode($response, true);

        foreach ($release['assets'] ?? [] as $asset) {
            if ($asset['name'] === $assetName) {
                $content = $this->makeRequest($asset['browser_download_url']);
                if (!is_dir($this->tempDir)) {
                    mkdir($this->tempDir, 0777, true);
                }
                $path = $this->tempDir . '/' . $assetName;
                file_put_contents($path, $content);
                return $path;
            }
        }

        throw new Exception("install.zip not found in latest release.");
    }

    private function extractAndInstall(string $zipPath): void {
        $zip = new ZipArchive;
        if ($zip->open($zipPath) !== true) {
            throw new Exception("Could not open ZIP file.");
        }

        $zip->extractTo($this->tempDir);
        $zip->close();

        $dirs = glob($this->tempDir . '/*', GLOB_ONLYDIR);
        if (empty($dirs)) {
            throw new Exception("No extracted folder found.");
        }

        $this->copyDirectory($dirs[0], $this->installDir);
    }

    private function copyDirectory(string $source, string $destination): void {
        $items = scandir($source);
        foreach ($items as $item) {
            if ($item === '.' || $item === '..') continue;
            $src = $source . '/' . $item;
            $dst = $destination . '/' . $item;
            if (is_dir($src)) {
                if (!is_dir($dst)) mkdir($dst, 0777, true);
                $this->copyDirectory($src, $dst);
            } else {
                copy($src, $dst);
            }
        }
    }

    private function makeRequest(string $url): string {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_USERAGENT => 'PHP Installer',
            CURLOPT_FOLLOWLOCATION => true,
        ]);
        $res = curl_exec($ch);
        if (curl_errno($ch)) throw new Exception(curl_error($ch));
        if (curl_getinfo($ch, CURLINFO_HTTP_CODE) !== 200) {
            throw new Exception("HTTP error: " . curl_getinfo($ch, CURLINFO_HTTP_CODE));
        }
        curl_close($ch);
        return $res;
    }

    private function copyEnvIfNeeded(): void {
        $envExample = $this->installDir . '/.env.example';
        $envFile    = $this->installDir . '/.env';

        if (!file_exists($envFile) && file_exists($envExample)) {
            copy($envExample, $envFile);
            echo ".env file created from .env.example\n";
        }
    }

    private function showReadmeHint(): void {
        $readmePath = $this->installDir . '/README.md';
        if (file_exists($readmePath)) {
            echo "\n--- README.md ---\n";
            $lines = file($readmePath);
            foreach (array_slice($lines, 0, 10) as $line) {
                echo rtrim($line) . "\n";
            }
            echo "...\n(See full README.md for details)\n\n";
        }
    }

    private function selfDestruct(): void {
        echo "Removing installer...\n";
        unlink(__FILE__);
    }

    private function cleanup(): void {
        if (is_dir($this->tempDir)) {
            $this->removeDirectory($this->tempDir);
        }
    }

    private function removeDirectory(string $dir): void {
        foreach (scandir($dir) as $item) {
            if ($item === '.' || $item === '..') continue;
            $path = $dir . '/' . $item;
            is_dir($path) ? $this->removeDirectory($path) : unlink($path);
        }
        rmdir($dir);
    }
}

// Execute
$installer = new Installer();
$installer->install();
                                             <?php

class Installer {
    private const REPO_OWNER = 'Matt-17';
    private const REPO_NAME  = 'Dropblog';

    private string $installDir;
    private string $tempDir;
    private string $githubApiUrl;

    public function __construct() {
        $this->installDir = dirname(__FILE__);
        $this->tempDir = $this->installDir . '/temp_install';
        $this->githubApiUrl = "https://api.github.com/repos/" . self::REPO_OWNER . "/" . self::REPO_NAME . "/releases/latest";
    }

    public function install(): bool {
        try {
            if (file_exists($this->installDir . '/version.txt')) {
                throw new Exception("Application is already installed.");
            }

            echo "Downloading install.zip...\n";
            $zipPath = $this->downloadReleaseAsset('install.zip');
            if (!$zipPath) {
                throw new Exception("Download failed.");
            }

            echo "Installing...\n";
            $this->extractAndInstall($zipPath);
            echo "Done. Check wwwroot/, and delete this file.\n";
            $this->selfDestruct();

        } catch (Exception $e) {
            echo "Error: " . $e->getMessage() . "\n";
            return false;
        } finally {
            $this->cleanup();
        }

        return true;
    }

    private function downloadReleaseAsset(string $assetName): ?string {
        $response = $this->makeRequest($this->githubApiUrl);
        $release = json_decode($response, true);

        foreach ($release['assets'] ?? [] as $asset) {
            if ($asset['name'] === $assetName) {
                $content = $this->makeRequest($asset['browser_download_url']);
                if (!is_dir($this->tempDir)) {
                    mkdir($this->tempDir, 0777, true);
                }
                $path = $this->tempDir . '/' . $assetName;
                file_put_contents($path, $content);
                return $path;
            }
        }

        throw new Exception("install.zip not found in latest release.");
    }

    private function extractAndInstall(string $zipPath): void {
        $zip = new ZipArchive;
        if ($zip->open($zipPath) !== true) {
            throw new Exception("Could not open ZIP file.");
        }

        $zip->extractTo($this->tempDir);
        $zip->close();

        $dirs = glob($this->tempDir . '/*', GLOB_ONLYDIR);
        if (empty($dirs)) {
            throw new Exception("No extracted folder found.");
        }

        $this->copyDirectory($dirs[0], $this->installDir);
    }

    private function copyDirectory(string $source, string $destination): void {
        $items = scandir($source);
        foreach ($items as $item) {
            if ($item === '.' || $item === '..') continue;
            $src = $source . '/' . $item;
            $dst = $destination . '/' . $item;
            if (is_dir($src)) {
                if (!is_dir($dst)) mkdir($dst, 0777, true);
                $this->copyDirectory($src, $dst);
            } else {
                copy($src, $dst);
            }
        }
    }

    private function makeRequest(string $url): string {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_USERAGENT => 'PHP Installer',
            CURLOPT_FOLLOWLOCATION => true,
        ]);
        $res = curl_exec($ch);
        if (curl_errno($ch)) throw new Exception(curl_error($ch));
        if (curl_getinfo($ch, CURLINFO_HTTP_CODE) !== 200) {
            throw new Exception("HTTP error: " . curl_getinfo($ch, CURLINFO_HTTP_CODE));
        }
        curl_close($ch);
        return $res;
    }

    private function selfDestruct(): void {
        echo "Removing installer...\n";
        unlink(__FILE__);
    }

    private function cleanup(): void {
        if (is_dir($this->tempDir)) {
            $this->removeDirectory($this->tempDir);
        }
    }

    private function removeDirectory(string $dir): void {
        foreach (scandir($dir) as $item) {
            if ($item === '.' || $item === '..') continue;
            $path = $dir . '/' . $item;
            is_dir($path) ? $this->removeDirectory($path) : unlink($path);
        }
        rmdir($dir);
    }
}

// Execute
$installer = new Installer();
$installer->install();
