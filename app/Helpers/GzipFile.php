<?php

namespace App\Helpers;

class GzipFile
{
    /**
     * zlib inflate status code Z_STREAM_END (stream completed successfully).
     * PHP does not define zlib status constants; Z_STREAM_END = 1.
     */
    private const INFLATE_STREAM_END = 1;

    /**
     * Check whether a file is gzip-compressed by inspecting the magic bytes (1F 8B).
     */
    public static function isGzip(string $path): bool
    {
        if (! is_file($path) || filesize($path) < 2) {
            return false;
        }

        $handle = fopen($path, 'rb');
        if (! $handle) {
            return false;
        }

        $header = fread($handle, 2);
        fclose($handle);

        return $header === "\x1f\x8b";
    }

    /**
     * Stream-decompress a gzip file to the destination path in 1MB chunks.
     * Uses the inflate API (not gzread) because gzread silently returns
     * garbage on corrupt gzip data instead of raising an error.
     * Returns the number of bytes written.
     *
     * @throws \RuntimeException
     */
    public static function decompressTo(string $gzPath, string $destPath): int
    {
        $source = fopen($gzPath, 'rb');
        if (! $source) {
            throw new \RuntimeException("Tidak dapat membuka file gzip: {$gzPath}");
        }

        $context = inflate_init(ZLIB_ENCODING_GZIP);
        if ($context === false) {
            fclose($source);
            throw new \RuntimeException('Gagal menginisialisasi decompressor gzip.');
        }

        $dest = fopen($destPath, 'wb');
        if (! $dest) {
            fclose($source);
            throw new \RuntimeException("Tidak dapat menulis file hasil decompress: {$destPath}");
        }

        $written = 0;
        $error = null;
        $streamEnded = false;

        try {
            while (! feof($source)) {
                $chunk = fread($source, 1024 * 1024);
                if ($chunk === false) {
                    $error = 'Gagal membaca file gzip.';

                    break;
                }
                if ($chunk === '') {
                    break;
                }

                // @ karena inflate_add memicu warning "data error" pada file corrupt
                $decompressed = @inflate_add($context, $chunk, ZLIB_NO_FLUSH);
                if ($decompressed === false) {
                    $error = 'File gzip corrupt atau tidak valid (CRC/data error).';

                    break;
                }

                $written += self::writeAll($dest, $decompressed, $destPath);

                if (inflate_get_status($context) === self::INFLATE_STREAM_END) {
                    $streamEnded = true;

                    break;
                }
            }

            // Stream belum berakhir setelah semua input dibaca = file terpotong.
            // ZLIB_FINISH pada stream yang SUDAH berakhir akan menghasilkan
            // Z_BUF_ERROR (-5), jadi hanya dipanggil saat stream belum selesai.
            if ($error === null && ! $streamEnded) {
                $final = @inflate_add($context, '', ZLIB_FINISH);
                if ($final === false || inflate_get_status($context) !== self::INFLATE_STREAM_END) {
                    $error = 'File gzip tidak lengkap atau corrupt (stream tidak berakhir dengan benar).';
                } else {
                    $written += self::writeAll($dest, $final, $destPath);
                }
            }
        } finally {
            fclose($source);
            fclose($dest);
        }

        if ($error !== null) {
            throw new \RuntimeException($error);
        }

        if ($written === 0) {
            throw new \RuntimeException('File gzip kosong atau tidak valid.');
        }

        return $written;
    }

    /**
     * Write the full string to an open handle, looping on partial writes.
     *
     * @throws \RuntimeException
     */
    private static function writeAll($handle, string $data, string $destPath): int
    {
        $length = strlen($data);
        $offset = 0;

        while ($offset < $length) {
            $bytes = fwrite($handle, substr($data, $offset));
            if ($bytes === false || $bytes === 0) {
                throw new \RuntimeException("Gagal menulis hasil decompress ke: {$destPath}");
            }
            $offset += $bytes;
        }

        return $length;
    }

    /**
     * Check whether file content looks like a SQL dump by scanning
     * the first $windowSize bytes for known dump markers.
     */
    public static function looksLikeSqlDump(string $path, int $windowSize = 65536): bool
    {
        if (! is_file($path)) {
            return false;
        }

        $handle = fopen($path, 'rb');
        if (! $handle) {
            return false;
        }

        $content = fread($handle, $windowSize);
        fclose($handle);

        if ($content === false || $content === '') {
            return false;
        }

        $markers = [
            'CREATE TABLE',
            'DROP TABLE',
            'INSERT INTO',
            '-- MySQL dump',
            '-- MariaDB dump',
            '-- PostgreSQL database dump',
            '/*!',
            'BEGIN;',
        ];

        foreach ($markers as $marker) {
            if (stripos($content, $marker) !== false) {
                return true;
            }
        }

        return false;
    }
}
