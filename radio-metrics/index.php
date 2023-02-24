<?php

require_once __DIR__ . '/vendor/autoload.php';

use React\EventLoop\Loop;
use Medoo\Medoo;
use Ridouchire\RadioMetrics\DataCollector;
use Ridouchire\RadioMetrics\Storage\Repository;
use Ridouchire\RadioMetrics\Storage\Artist\Artist;
use Ridouchire\RadioMetrics\Storage\Track\Track;
use Ridouchire\RadioMetrics\Storage\Playlist\Playlist;
use Ridouchire\RadioMetrics\Storage\Record\Record;

$db = new Medoo([
    'database_type' => 'mysql',
    'database_name' => $_ENV['MYSQL_DATABASE'],
    'server'        => $_ENV['MYSQL_HOSTNAME'],
    'username'      => $_ENV['MYSQL_USERNAME'],
    'password'      => $_ENV['MYSQL_PASSWORD'],
    'charset'       => 'utf8',
    'collation'     => 'utf8_unicode_ci'
]);

$collector = new DataCollector($_ENV['RADIO_API_URL']);
$repository = new Repository($db);

Loop::addPeriodicTimer(1, function () use ($repository, $collector, &$current_data) {
    $data = $collector->getData();

    if (isset($data['error'])) {
        return;
    }

    try {
        $artist = $repository->findOne(Artist::draft(), ['artist' => $data['artist']]);
    } catch (\DomainException) {
        $artist = Artist::draft($data['artist']);
    }

    try {
        $track = $repository->findOne(Track::draft(), ['track' => $data['track']]);
    } catch (\DomainException) {
        $track = Track::draft($data['track']);
    }

    try {
        $playlist = $repository->findOne(Playlist::draft(), ['playlist' => $data['playlist']]);
    } catch (\DomainException) {
        $playlist = Playlist::draft($data['playlist']);
    }

    if (
        isset($current_data['artist'])
        && $current_data['artist'] !== $data['artist']
    ) {
        $artist->play_count = $artist->play_count + 1;
        $artist->last_playing = time();
    }

    if (
        isset($current_data['track'])
        && $current_data['track'] !== $data['track']
    ) {
        $track->play_count = $artist->play_count + 1;
        $track->last_playing = time();
    }

    if (
        isset($current_data['playlist'])
        && $current_data['playlist'] !== $data['playlist']
    ) {
        $playlist->play_count = $artist->play_count + 1;
        $playlist->last_playing = time();
    }

    if (
        isset($current_data['listeners'])
        && $current_data['listeners'] !== $data['listeners']
        && (
            $current_data['track'] == $data['track']
            && $current_data['artist'] == $data['artist']
        )
    ) {
        if ($current_data['listeners'] > $data['listeners']) {
            $artist->estimate   = $artist->estimate - 1;
            $track->estimate    = $track->estimate - 1;
            $playlist->estimate = $playlist->estimate - 1;
        }
    }

    if ($data['listeners'] !== 0) {
        $artist->estimate   = $artist->estimate + 1;
        $track->estimate    = $track->estimate + 1;
        $playlist->estimate = $playlist->estimate + 1;
    }

    $repository->save($artist);
    $repository->save($track);
    $repository->save($playlist);
    $repository->save(Record::draft($artist->id, $track->id, $playlist->id, $data['listeners']));

    $current_data = $data;
});
