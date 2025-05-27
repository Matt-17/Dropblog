<?php                              
use PainBlog\Utils\HashIdHelper;

/**
 * Gibt die deutschen Monatsnamen zurück
 * 
 * @return array Array mit Monatsnamen (1-12)
 */
function get_month_names() {
    return [
        1 => 'Januar', 2 => 'Februar', 3 => 'März', 4 => 'April',
        5 => 'Mai', 6 => 'Juni', 7 => 'Juli', 8 => 'August',
        9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Dezember'
    ];
}

/**
 * Berechnet den vorherigen Monat
 * 
 * @param int $month Aktueller Monat (1-12)
 * @param int $year Aktuelles Jahr
 * @return array Array mit ['month' => int, 'year' => int]
 */
function get_previous_month($month, $year) {
    $prevMonth = $month - 1;
    $prevYear = $year;
    if ($prevMonth < 1) {
        $prevMonth = 12;
        $prevYear--;
    }
    return ['month' => $prevMonth, 'year' => $prevYear];
}

/**
 * Berechnet den nächsten Monat
 * 
 * @param int $month Aktueller Monat (1-12)
 * @param int $year Aktuelles Jahr
 * @return array Array mit ['month' => int, 'year' => int]
 */
function get_next_month($month, $year) {
    $nextMonth = $month + 1;
    $nextYear = $year;
    if ($nextMonth > 12) {
        $nextMonth = 1;
        $nextYear++;
    }
    return ['month' => $nextMonth, 'year' => $nextYear];
}

/**
 * Prüft, ob ein Monat in der Zukunft liegt
 * 
 * @param int $month Monat (1-12)
 * @param int $year Jahr
 * @return bool True wenn der Monat in der Zukunft liegt
 */
function is_future_month($month, $year) {
    $currentYear = date('Y');
    $currentMonth = date('n');
    return $year > $currentYear || ($year == $currentYear && $month > $currentMonth);
}

/**
 * Erstellt die SQL-Abfrage für die Gruppierung von Posts
 * 
 * @param PDO $pdo Die PDO-Datenbankverbindung
 * @param string $where_clause Die WHERE-Bedingung für die SQL-Abfrage
 * @param array $params Die Parameter für die SQL-Abfrage
 * @return array Die gruppierten Posts
 */
function get_grouped_posts($pdo, $where_clause, $params = []) {
    $sql = "
        SELECT 
            DATE(created_at) as post_date,
            GROUP_CONCAT(
                CONCAT(id, ':', content, ':', created_at)
                ORDER BY created_at DESC
                SEPARATOR '||'
            ) as posts
        FROM posts 
        WHERE $where_clause
        GROUP BY DATE(created_at)
        ORDER BY post_date DESC
    ";
    
    if (empty($params)) {
        $stmt = $pdo->query($sql);
    } else {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
    }
    
    $results = $stmt->fetchAll();
    $groupedPosts = [];
    
    // Verarbeite die gruppierten Posts
    foreach ($results as $row) {
        $date = $row['post_date'];
        $posts = [];
        $postStrings = explode('||', $row['posts']);
        foreach ($postStrings as $postString) {
            list($id, $content, $created_at) = explode(':', $postString);
            $posts[] = [
                'id' => (int)$id,
                'content' => $content,
                'date' => $date
            ];
        }
        $groupedPosts[$date] = $posts;
    }
    
    return $groupedPosts;
}

/**
 * Formatiert ein Datum im deutschen Format
 * 
 * @param string $date Datum im Format YYYY-MM-DD
 * @return string Formatiertes Datum
 */
function format_date($date) {
    $timestamp = strtotime($date);
    return date('d.', $timestamp) . ' ' . get_month_names()[date('n', $timestamp)] . ' ' . date('Y', $timestamp);
}

/**
 * Holt einen Post anhand seiner URL
 *                                                            
 * @param PDO    $pdo PDO-Instanz
 * @param string $url Die 8-stellige URL-ID
 * @return array|null Array mit ['id','content','date'] oder null
 */
function get_post_by_id($pdo, $id) {
    $stmt = $pdo->prepare("                    
        SELECT 
            p.id,
            p.content,
            DATE_FORMAT(p.created_at, '%Y-%m-%d') as date
        FROM posts p
        WHERE p.id = ? 
          AND p.created_at <= NOW()
        LIMIT 1
    ");
    $stmt->execute([$id]);
    return $stmt->fetch();
}

/**
 * Konvertiert Markdown zu HTML
 */
function markdown_to_html($markdown) {
    static $parsedown = null;
    
    if ($parsedown === null) {
        $parsedown = new Parsedown();
        $parsedown->setSafeMode(true); // XSS-Schutz aktivieren
    }
    
    return $parsedown->text($markdown);
}

/**
 * Konvertiert eine ID in eine URL-kompatible Zeichenkette
 * 
 * @param int $id Die ID des Posts
 * @return string Die konvertierte ID
 */
function id_to_url($id) {
    return HashIdHelper::encode($id);
}