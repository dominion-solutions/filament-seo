<?php

declare(strict_types=1);

/**
 * Verify that every relative link in the documentation resolves to a real file.
 *
 * Markdown that links to a diagram or a guide which has been moved or renamed
 * still renders on GitHub — it just quietly shows a broken image or a dead
 * link. This catches that in CI instead of in a review.
 *
 * External links (http/https/mailto) and pure anchors are skipped: this checks
 * that the repository's own paths are correct, not that the internet is up.
 *
 * Usage: php bin/check-doc-links.php [path ...]
 */
$root = dirname(__DIR__);

/**
 * Directories that hold other people's Markdown.
 *
 * @var list<string>
 */
$ignored = ['vendor', 'node_modules', 'build', '.build', '.git', '.idea', '.vscode'];

$defaultPaths = ['docs', '.'];

/** @var list<string> $paths */
$paths = array_slice($argv, 1) ?: $defaultPaths;

$files = [];

/**
 * Collect Markdown files under a path, skipping ignored directories.
 */
$collect = function (string $path) use ($ignored, &$files): void {
    if (is_file($path)) {
        if (strtolower(pathinfo($path, PATHINFO_EXTENSION)) === 'md') {
            $files[] = $path;
        }

        return;
    }

    if (! is_dir($path)) {
        fwrite(STDERR, "No such path: {$path}\n");

        exit(1);
    }

    $iterator = new RecursiveCallbackFilterIterator(
        new RecursiveDirectoryIterator($path, FilesystemIterator::SKIP_DOTS),
        function (SplFileInfo $current) use ($ignored): bool {
            if (! $current->isDir()) {
                return true;
            }

            return ! in_array($current->getFilename(), $ignored, true);
        }
    );

    foreach (new RecursiveIteratorIterator($iterator) as $file) {
        if ($file->isFile() && strtolower($file->getExtension()) === 'md') {
            $files[] = $file->getPathname();
        }
    }
};

foreach ($paths as $path) {
    $collect(str_starts_with($path, '/') ? $path : $root.'/'.trim($path, '/'));
}

// The root path and the docs path overlap; compare on absolute path.
$files = array_values(array_unique($files));
sort($files);

$checked = 0;
$broken = 0;

foreach ($files as $file) {
    $directory = dirname($file);
    $relative = str_replace($root.'/', '', $file);

    $lines = preg_split('/\R/', (string) file_get_contents($file)) ?: [];

    foreach ($lines as $index => $line) {
        if (preg_match_all('/\[[^\]]*\]\(([^)\s]+)\)/', $line, $matches) === 0) {
            continue;
        }

        foreach ($matches[1] as $link) {
            if (preg_match('~^(?:https?://|mailto:|\#)~', $link) === 1) {
                continue;
            }

            $target = explode('#', $link)[0];

            if ($target === '') {
                continue;
            }

            $checked++;

            $resolved = str_starts_with($target, '/')
                ? $root.$target
                : $directory.'/'.$target;

            if (! file_exists($resolved)) {
                $broken++;
                printf("BROKEN  %s:%d  ->  %s\n", $relative, $index + 1, $link);
            }
        }
    }
}

printf("Checked %d relative link(s) across %d file(s), %d broken.\n", $checked, count($files), $broken);

exit($broken > 0 ? 1 : 0);
