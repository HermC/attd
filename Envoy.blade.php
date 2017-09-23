@servers(['localhost' => '127.0.0.1'])

@task('deploy', ['on' => 'localhost'])

cd /data/wwwroot/attendance
git reset --hard
git pull

@endtask