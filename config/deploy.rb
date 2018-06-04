# config/deploy.rb file
require 'bundler/capistrano'

set :application, "user-api"
set :deploy_to, ENV["DEPLOY_PATH"]
server  ENV["SERVER_NAME"], :app, :web

gateway = ENV["GATEWAY"]
unless gateway.nil?
  set :gateway, ENV["GATEWAY"]
end

set :user, "dosomething"
set :group, "dosomething"
set :use_sudo, false

set :repository, "."
set :scm, :none
set :deploy_via, :copy
set :keep_releases, 5

ssh_options[:keys] = [ENV["CAP_PRIVATE_KEY"]]

default_run_options[:shell] = '/bin/bash'

namespace :deploy do
  folders = %w{logs dumps system}

  task :link_folders do
    run "ln -nfs #{shared_path}/.env #{release_path}/"
    rm -rf "#{release_path}/storage/app/keys"
    run "ln -nfs #{shared_path}/keys #{release_path}/storage/app/keys"
    folders.each do |folder|
      run "rm -rf #{release_path}/storage/#{folder}"
      run "ln -nfs #{shared_path}/#{folder} #{release_path}/storage/#{folder}"
    end
  end

  task :artisan_migrate do
    run "cd #{release_path} && php artisan migrate --force"
  end

  task :artisan_cache_clear do
    run "cd #{release_path} && php artisan cache:clear"
  end

  task :artisan_queue_restart do
    run "cd #{release_path} && php artisan queue:restart"
  end

  task :restart_php do
    run "sudo /usr/sbin/service php7.0-fpm restart"
  end
end

after "deploy:update", "deploy:cleanup"
after "deploy:symlink", "deploy:link_folders"
after "deploy:link_folders", "deploy:artisan_migrate"
after "deploy:artisan_migrate", "deploy:artisan_cache_clear"
after "deploy:artisan_cache_clear", "deploy:artisan_queue_restart"
after "deploy:artisan_queue_restart", "deploy:restart_php"

