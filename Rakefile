namespace "db" do
  namespace "sync" do
    task "up" do
      timestamp = Time.now.strftime('%Y%m%d%H%I%S')
      `mkdir -p working/db`
      `mysqldump -u root -proot jesters | gzip -9 > working/db/#{timestamp}.sql.gz`
      `git add working/db/#{timestamp}.sql.gz`
      `git commit -m "pushing database to staging site" && git push origin master`
      `ssh robo.tk "cd /var/www/jesters; git pull origin master; gunzip < working/db/#{timestamp}.sql.gz | sed 's/jesters.local/jesters.matt-powell.org.nz/g' | mysql -u jesters -psp4c3jump jesters"`
    end

    task "down" do
      timestamp = Time.now.strftime('%Y%m%d%H%I%S')
      `ssh robo.tk "cd /var/www/jesters; mkdir -p working/db; mysqldump -u jesters -psp4c3jump jesters | sed 's/jesters.matt-powell.org.nz/jesters.local/g' | gzip -9 > working/db/#{timestamp}.sql.gz"`
      `ssh robo.tk "cd /var/www/jesters; git add working/db/#{timestamp}.sql.gz && git commit -m 'syncing changes to development database' && git push origin master"`
      `git pull origin master`
      `gunzip < working/db/#{timestamp}.sql.gz | mysql -u root -proot jesters`
    end
    
    task "clean" do
      `git rm working/db/*.sql.gz && git commit -m "clearing out old db backups" && git push origin master`
    end
  end
end

namespace "staging" do
  task "update" do
    `ssh robo.tk "cd /var/www/jesters && git pull origin master"`
  end
end