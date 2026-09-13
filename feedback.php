<?php
// Antworten von abschied.html (de/en/es) -> Mail an contact@speechpilot.vip.
// Kein Speicher, kein Konto: was hier ankommt, geht als Mail raus und ist weg.
$sprache = in_array($_POST['sprache'] ?? '', ['en', 'es'], true) ? $_POST['sprache'] : 'de';
$zurueck = ($sprache === 'de' ? '/' : "/$sprache/") . 'abschied.html';

// Honigtopf: das Feld "website" ist unsichtbar - wer es fuellt, ist ein Bot.
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || ($_POST['website'] ?? '') !== '') {
  header("Location: $zurueck#danke", true, 303);
  exit;
}

$grund = in_array($_POST['grund'] ?? '', ['erkennung', 'kompliziert', 'unnoetig'], true) ? $_POST['grund'] : '-';
$text = trim(mb_substr((string)($_POST['text'] ?? ''), 0, 2000));
$version = preg_replace('/[^0-9.]/', '', (string)($_POST['v'] ?? ''));
$mail = filter_var(trim((string)($_POST['mail'] ?? '')), FILTER_VALIDATE_EMAIL) ?: '';

// Nichts angekreuzt und nichts geschrieben: keine leere Mail.
if ($grund === '-' && $text === '') {
  header("Location: $zurueck#danke", true, 303);
  exit;
}

$inhalt = "Grund: $grund\nSprache: $sprache\nVersion: " . ($version ?: '-') .
          "\nMail: " . ($mail ?: '-') . "\n\n$text\n";
$kopf = "From: SpeechPilot <contact@speechpilot.vip>\r\nContent-Type: text/plain; charset=utf-8";
if ($mail) $kopf .= "\r\nReply-To: $mail";   // FILTER_VALIDATE_EMAIL laesst keine Zeilenumbrueche durch

$ok = mail('contact@speechpilot.vip', "=?UTF-8?B?" . base64_encode("Deinstallation: $grund") . "?=", $inhalt, $kopf);
header("Location: $zurueck#" . ($ok ? 'danke' : 'fehler'), true, 303);
