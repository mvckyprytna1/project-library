<?php
declare(strict_types=1);

function e(?string $value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function url(string $path = ''): string
{
    $base = rtrim(APP_BASE_URL, '/');
    $path = ltrim($path, '/');

    if ($path === '') {
        return $base === '' ? '/' : $base . '/';
    }

    return ($base === '' ? '' : $base) . '/' . $path;
}

function redirect(string $path): never
{
    header('Location: ' . url($path));
    exit;
}

function current_path(): string
{
    return basename(parse_url($_SERVER['SCRIPT_NAME'] ?? '', PHP_URL_PATH) ?: '');
}

function is_active(string $file): string
{
    return current_path() === $file ? 'active' : '';
}

function set_flash(string $type, string $message): void
{
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function get_flash(): ?array
{
    if (!isset($_SESSION['flash'])) {
        return null;
    }

    $flash = $_SESSION['flash'];
    unset($_SESSION['flash']);

    return $flash;
}

function slugify(string $text): string
{
    $text = preg_replace('~[^\pL\d]+~u', '-', $text);
    $text = trim((string)$text, '-');
    $text = strtolower($text);
    $text = preg_replace('~[^-\w]+~', '', $text);

    return $text !== '' ? $text : 'item';
}

function unique_slug(PDO $pdo, string $table, string $title, ?int $ignoreId = null): string
{
    $base = slugify($title);
    $slug = $base;
    $i = 2;

    while (true) {
        $sql = "SELECT id FROM {$table} WHERE slug = ?";
        $params = [$slug];

        if ($ignoreId !== null) {
            $sql .= " AND id != ?";
            $params[] = $ignoreId;
        }

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        if (!$stmt->fetch()) {
            return $slug;
        }

        $slug = $base . '-' . $i;
        $i++;
    }
}

function status_options(): array
{
    return [
        'live'        => 'Live',
        'draft'       => 'Draft',
        'development' => 'Development',
        'bug'         => 'Bug/Error',
        'archived'    => 'Archived',
    ];
}

function status_label(string $status): string
{
    $options = status_options();
    return $options[$status] ?? 'Unknown';
}

function status_class(string $status): string
{
    return match ($status) {
        'live' => 'badge badge-live',
        'draft' => 'badge badge-draft',
        'development' => 'badge badge-development',
        'bug' => 'badge badge-bug',
        'archived' => 'badge badge-archived',
        default => 'badge',
    };
}

function is_valid_url(?string $url): bool
{
    if ($url === null || trim($url) === '') {
        return true;
    }

    return filter_var($url, FILTER_VALIDATE_URL) !== false;
}

function normalize_url(?string $url): ?string
{
    $url = trim((string)$url);

    if ($url === '') {
        return null;
    }

    return $url;
}

function excerpt(?string $text, int $length = 130): string
{
    $text = trim(strip_tags((string)$text));

    if (mb_strlen($text) <= $length) {
        return $text;
    }

    return mb_substr($text, 0, $length) . '...';
}

function format_date(?string $date): string
{
    if (!$date) {
        return '-';
    }

    return date('d M Y H:i', strtotime($date));
}

function upload_thumbnail(array $file, ?string $oldFile = null): ?string
{
    if (!isset($file['tmp_name']) || $file['error'] === UPLOAD_ERR_NO_FILE) {
        return $oldFile;
    }

    if ($file['error'] !== UPLOAD_ERR_OK) {
        throw new RuntimeException('Upload gagal. Coba ulangi file gambarnya.');
    }

    if ($file['size'] > MAX_UPLOAD_SIZE) {
        throw new RuntimeException('Ukuran thumbnail maksimal 2MB.');
    }

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($file['tmp_name']);

    if (!array_key_exists($mime, ALLOWED_IMAGE_MIME)) {
        throw new RuntimeException('Format thumbnail harus jpg, jpeg, png, atau webp.');
    }

    if (!is_dir(UPLOAD_PATH)) {
        mkdir(UPLOAD_PATH, 0755, true);
    }

    $extension = ALLOWED_IMAGE_MIME[$mime];
    $filename = 'project_' . date('Ymd_His') . '_' . bin2hex(random_bytes(6)) . '.' . $extension;
    $destination = UPLOAD_PATH . $filename;

    if (!move_uploaded_file($file['tmp_name'], $destination)) {
        throw new RuntimeException('Gagal menyimpan thumbnail ke folder upload.');
    }

    if ($oldFile && is_file(UPLOAD_PATH . $oldFile)) {
        @unlink(UPLOAD_PATH . $oldFile);
    }

    return $filename;
}

function delete_thumbnail(?string $filename): void
{
    if ($filename && is_file(UPLOAD_PATH . $filename)) {
        @unlink(UPLOAD_PATH . $filename);
    }
}

function thumbnail_url(?string $filename): string
{
    if ($filename && is_file(UPLOAD_PATH . $filename)) {
        return url(UPLOAD_URL . $filename);
    }

    return url('assets/img-placeholder.svg');
}

function require_post(): void
{
    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
        http_response_code(405);
        exit('Method not allowed.');
    }
}

function checked(bool $value): string
{
    return $value ? 'checked' : '';
}
