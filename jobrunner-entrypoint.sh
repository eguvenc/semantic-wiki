#!/bin/sh

kill_runner() {
    kill "$PID" 2> /dev/null
}
trap kill_runner SIGTERM

while true; do
    php maintenance/run.php runJobs --wait --maxjobs=100 --conf /config/LocalSettings.php &
    PID=$!
    wait "$PID"
done