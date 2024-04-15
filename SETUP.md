# WordPress project bootstrap

## Prerequisites

Login to the Google Cloud Platform. The authentication token expires
after a while, so you may need to re-authenticate every now and then.

```
gcloud auth login
```

Enable docker to use private artifact registries with gcloud
authentication. This is one-time setup for the host environment.
If you have already done this for another project, you can skip
this step.

```
gcloud auth configure-docker europe-docker.pkg.dev
```

## Project name

The `client-xyz` is used as an example project name and
GCP project id in the following instructions. Replace it
with your own project name.

Best practice is to prefix client project names with `client-`,
and internal projects with `hion`. Use short but descriptive
names. For internal sandbox projects (eg. for testing)
use prefix like `hion-demo-`, `hion-sandbox-` or something that makes
it distinguiquishable from a production project, and include your
name or initials in the project name for easier identification,
eg. `hion-demo-pnu`.

The GitHub repository name should be the same as GCP project id.
GCP project id must be globally unique and cannot be changed later.
Note: the local working directoty name and GitHub repository name
can be changed later, if the desired name is not available in GCP.

## Create a new bedrock project

```
composer create-project roots/bedrock client-xyz
cd client-xyz
composer update
```

```
git init
git add --all
git commit -m 'initial commit'
```

## Configure the project

```
composer require wp-cli/wp-cli-bundle
git commit -am 'require wp-cli'
```

## WP_HOME / WP_SITEURL automatic discovery

Edit `config/application.php` to do host auto discovery.
Remove this line in Dotenv definition:

```
$dotenv->required(['WP_HOME', 'WP_SITEURL']);
```

Replace these lines in "URLSs" section:

```
Config::define('WP_HOME', env('WP_HOME'));
Config::define('WP_SITEURL', env('WP_SITEURL'));
```

with:

```
if (array_key_exists('HTTP_X_FORWARDED_PROTO', $_SERVER) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') {
    $_SERVER['HTTPS'] = 'on';
}
$_server_http_host_scheme = array_key_exists('HTTPS', $_SERVER) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
$_server_http_host_name   = array_key_exists('HTTP_HOST', $_SERVER) ? $_SERVER['HTTP_HOST'] : (env('WP_DEFAULT_HTTP_HOST') ?: 'localhost:8080');
$_server_http_url         = "$_server_http_host_scheme://$_server_http_host_name";
Config::define('WP_HOME', env('WP_HOME') ?: "$_server_http_url");
Config::define('WP_SITEURL', env('WP_SITEURL') ?: "$_server_http_url/wp");
```

```
git commit -am 'autodiscover WP_HOME and WP_SITEURL'
```

## Git ignore

Ignore composer installed drop-ins:

```
cat >>.gitignore <<EOD

# Drop-ins
/web/app/advanced-cache.php
/web/app/object-cache.php
/web/app/db.php
EOD
```

```
git commit -am 'ignore composer installed drop-ins'
```

## Redis

```
composer require wpackagist-plugin/redis-cache
```

Add to `config/application.php` after "DB settings":

```
/**
 * Redis settings
 */
Config::define('WP_REDIS_DISABLED', env('WP_REDIS_DISABLED'));
Config::define('WP_REDIS_HOST', env('REDISHOST') ?: env('WP_REDIS_HOST') ?: '127.0.0.1');
Config::define('WP_REDIS_PORT', env('REDISPORT') ?: env('WP_REDIS_PORT') ?: '6379');
Config::define('WP_REDIS_DATABASE', env('WP_REDIS_DATABASE') ?: '0');
```

Add to `scripts` in `composer.json`:

```
"setup-drop-ins": [
    "cp web/app/plugins/redis-cache/includes/object-cache.php web/app/object-cache.php"
],
"post-install-cmd": [
    "@setup-drop-ins"
],
"post-update-cmd": [
    "@setup-drop-ins"
]
```

```
git commit -am 'configure redis'
```

## Query Monitor

```
composer require wpackagist-plugin/query-monitor
```

Add to `scripts/setup-drop-ins` in `composer.json`:

```
"ln -fs $(pwd)/web/app/plugins/query-monitor/wp-content/db.php web/app/db.php"
```

Add to `config/application.php` after "Redis settings":

```
/**
 * Query Monitor settings
 */
Config::define('QM_DISABLED', env('QM_DISABLED'));
```

```
git commit -am 'configure query monitor'
```

## Auth0 4.x plugin installation

```
composer require wpackagist-plugin/auth0
```

```
git commit -am 'add auth0 4.x'
```

## Auth0 5.x plugin installation (optional, advanced)

```
composer require wpackagist-plugin/auth0:dev-master
```

Add to extra/installer-paths in `composer.json`:

```
"web/app/plugins/auth0-wordpress": [
  "auth0/wordpress"
],
```

Add to scripts/setup-drop-ins in `composer.json`:

```
"patch -N -d web/app/plugins/auth0-wordpress -p 1 < hotfix-auth0-wordpress-pr-874.diff || true"
```

Create file `hotfix-auth0-wordpress-pr-874.diff`:

```
--- a/src/Cache/WpObjectCacheItem.php
+++ b/src/Cache/WpObjectCacheItem.php
@@ -42,7 +42,7 @@ final class WpObjectCacheItem implements CacheItemInterface
         return $this;
     }

-    public function expiresAt(?\DateTimeInterface $expiration)
+    public function expiresAt(?\DateTimeInterface $expiration): static
     {
         if ($expiration instanceof DateTimeInterface) {
             $this->expires = $expiration->getTimestamp();
```

See https://github.com/auth0/wordpress/pull/874 for more information.
The patch above should not be needed after next realease of the plugin.

```
git add hotfix-auth0-wordpress-pr-874.diff
git commit -am 'add auth0 5.x'
```

## Auth0 minimal setup (optional)

Create a new Auth0 application in the Auth0 dashboard.
This may be in a new tenant, or in the same tenant as
other services, depending on the use case.

Activate the plugin and go the the basic settings page.
Set the "domain", "client id" and "client secret" values
from the Auth0 application settings page.

Add `http://localhost:8080/wp/index.php?auth0=1` to the
list of allowed callback URLs, and `http://localhost:8080`
to the list of allowed logout URLs in the Auth0 application
settings page. Save changes.

You can now login with a google account if the email
address matches the one in the WP user profile. You can
bypass the auth0 login by adding `?wle` to the URL.

After setting up the Cloud Run environment, or after
setting up the custom domain you need to add the new
allowed callback URLs to the Auth0 application settings,
or create a new application (with unique client id, secret
and settings) for these new environments.

Note: when attempting to login with Auth0, the mismatching
callback URLs are clearly shown in the error message. You can
then easily add the correct URLs to the Auth0 application
settings.

## Satis composer repository

Add Hion Satis repository to composer.json / repositories section.

```
{
  "type": "composer",
  "url": "https://satis.hion.dev"
}
```

```
git commit -am 'add satis repository'
```

See https://github.com/devgeniem/hiondev-satis-repository for
more information. Most paid / premium plugins can be installed from
the Satis repository.

## Docker configuration

Create a `docker-compose.yaml`, `Dockerfile` and various
configuration files to the root of the project. This one-liner
will copy the files from hiondev container image to the project
working directory. Alternatively you can copy the
files manually from the hiondev github repository.

```
docker run --pull=always --rm --volume=.:/app \
  europe-docker.pkg.dev/hiondev/hiondev/cli \
  cp -r /hiondev/eg/.editorconfig /hiondev/eg/docker-compose.yaml \
  /hiondev/eg/Dockerfile /hiondev/eg/wp/. /app
```

```
git add --all
git commit -m 'initial hiondev setup'
```

## Docker ignore

```
cp .gitignore .dockerignore
git commit -am 'add initial .dockerignore'
```

This is required for the local "staging" and "production"
builds to work reliably, eg. to not include any locally
installed node or composer modules.

Docker builds should not use any locally generated files,
so copying the ignore file from `.gitignore` is a reasonable
default.

However, docker and git do not interpret all ignore rules in the
same way. This additional rule is required to verify that
`bedrock-autoload.php` is _not_ ignored by docker:

```
echo "!web/app/mu-plugins/*.php" >>.dockerignore
git commit -am 'do not dockerignore web/app/mu-plugins/*.php'
```

# Local development environment setup

## Create empty local database

```
docker compose up --detach db
docker compose exec db mysqladmin create staging
```

## Starting local dev

```
docker compose build dev
docker compose up --detach dev
```

```
docker compose exec dev sh
# composer install
# ...
```

WP is now accessible at `http://localhost:8080`.

## Running wp-cli in local dev

```
docker compose exec dev sh
# wp user list
# ...
```

## Example: Local dev env

Modify `.env.app` and restart dev container with
`docker compose up --detach dev` to apply changes.

After creating the bucket (see below) you should
change the GCS_UPLOADS_BUCKET value to match the
bucket name.

## Stopping local dev

```
docker compose down --remove-orphans
```

## Run one-off commands in dev

You can use `docker compose run --rm ...` to run
one-off commands in the containers without
starting (up) the container first. For example:

```
docker compose run --rm dev composer require acf/advanced-custom-fields-pro
git commit -am 'add acf pro'
```

## Cloud Trace with OpenTelemetry

Require OTEL modules to enable automatic tracing.
This will not start tracing, but enables it to
be configured with environment variables (later).

```
docker compose run --rm dev composer require \
  open-telemetry/sdk \
  open-telemetry/opentelemetry-auto-io \
  open-telemetry/opentelemetry-auto-wordpress \
  open-telemetry/exporter-otlp \
  php-http/guzzle7-adapter
```

```
git commit -am 'add support for OTEL instrumentation'
```

## Developing the OTEL instrumentation locally (optional, advanced)

Stop service `dev` (if running), start `dev-trace` instead
(the only difference is in the environment variables, see
`docker-compose.yaml` for details). Start service `jaeger`.

```
docker compose down dev
docker compose up --detach jaeger dev-trace
```

Open `http://localhost:16686/` to examine the traces
and other instrumentation with the Jaeger.

# GCP environment and hiondev setup

## Create a project

Go to https://console.cloud.google.com/projectcreate
to create a new project. Use the same `client-xyz` as
above or choose a new id and use it in the following
steps. Note: project _id_ must be globally unique and
cannot be changed later. However, project _name_ can be
changed later.

Create project to the "Sandbox" folder and link the
"Hion Sandbox" billing account to the project.
Alternatively, this can be done with gcloud CLI:

```
gcloud projects create client-xyz --folder=943559366979
gcloud beta billing projects link client-xyz --billing-account=01D6F1-596A9E-33E5CC
```

## gcloud CLI defaults

Set your local `gcloud` commands to use this new project
as the default.

```
gcloud config set project client-xyz
```

Define run and build region defaults. If you have already done
this for another project, you can skip this step. You can also
use options `--project=client-xyz` and `--region=europe-north1`
in all `gcloud` commands for the same effect.

```
gcloud config set run/region europe-north1
gcloud config set builds/region europe-west1
```

Note: these apply only to the local `gcloud` (ie. running in your
host MacOS environment). The `hiondev` scripts runs in a docker
container that has a separate gcloud configuration, eg. login and
project id. Ie. `hiondev` scripts use region settings defined in
the _project configuration_ instead of host `gcloud` config
defaults defined above.

## Hiondev setup

The basic usage pattern for a hiondev command is
`docker compose run --rm hiondev <command> <args>`.
You may want to alias it. Documentation assumes
that an alias `hiondev` is used, eg.

```
alias hiondev='docker compose run --rm hiondev'
```

Note: commands are run in the container, so the
`<args>` are relative to the container filesystem.
The project root is mounted to `/app` inside the
container.

## Hiondev update and login

The `hiondev` command shell has it's own configuration
storage / state, so it needs to be updated and logged in
separately from the host environment. Use the
`hiondev login` command to login it to the GCP.

```
docker compose pull hiondev
hiondev login client-xyz
```

## Initialize the GCP project

These one-time commands initialize the project
configuration and create the required resources
during the project setup.

```
hiondev setup-hiondev-config
hiondev setup-artifact-registry staging
hiondev setup-run-service-account staging
hiondev setup-bucket staging
hiondev setup-dot-env-secrets
```

Note: configure the bucket name in `.env.app`
and apply the changes with `docker compose restart dev`
to make the bucket available in the local dev environment.

## Cloud SQL database

```
hiondev setup-cloudsql staging
hiondev db-upload staging staging staging
```

(Run `hiondev db-upload staging` to view available databases.)

## Deploy to Cloud Run

```
hiondev deploy staging
```

Setup some initial environment variables for the
deployed service:

```
hiondev setup-run-env-init staging
```

Note! This will overwrite all environment
variables in the Cloud Run service. This command is
typically used only for the initial deploy. 

## Example: modifying environment variables (optional)

Modifying the environment variables and other
parameters after the initial setup is done directly
with gcloud CLI, eg.

```
gcloud run services describe app-staging
gcloud run services update app-staging --update-env-vars=FOO=bar
```

## Cloud Run Jobs worker and wp-cron scheduler

Create a worker Job. This copies environment variables
from the app service. Run `setup-worker-env` again after
modifying the app service environment variables.

```
hiondev setup-worker staging
hiondev setup-worker-env staging
```

Schedule a wp-cron task. Observe the command output
if there is a need to modify the schedule or
the command.

```
hiondev setup-worker-cron staging
```

## Example: worker usage (optional)

Worker can be used to run (non-interactively) any
commands on the cloud run environment. For example,
to build redisearch indexes, or any wp-cli commands.

```
gcloud run jobs execute app-staging-worker --args=wp,user,list
```

See below how to run wp-cli interactively in local env,
but against production database.

## Cloud Build repository connections

Add a repository connection for the Cloud Build triggers.

GitHub user "Hion Bob the Builder Bot" (https://github.com/Bob-the-Builder-Bot)
is used to access the GitHub repository. You'll have to give Admin access to
the Git repository for this user before creating the connection. The connection
is created with a specific GitHub App (Google Cloud Build) _installation id_
and GitHub access token for the Bot account, which is stored in Secret Manager.
Ie. use the values below to access GitHub repositories with the Hion Bob the
Builder Bot.

```
hiondev setup-github-connection 8288619 projects/hiondev/secrets/devgeniem-github-oauthtoken
```

> Note: Cloud Build _service agent_ is given secretAccessor role to the token
> secret, which may be stored outside of this project (eg. project "hiondev").
> This is required for the Cloud Build to access the token secret, to use it
> to call GitHub APIs on behalf of the Bot account, to configure the GitHub app.

The connection is then used to connect a repository. It can
be used by the whole project team (with sufficient IAM permissions) and
to connect multiple repositories to the same project.

```
hiondev setup-github-repo 8288619 xyz https://github.com/devgeniem/client-xyz.git
```

The existing github connections and repositories can be found from the
GCP console and gcloud CLI.

```
gcloud builds connections list
gcloud builds repositories list --connection=gh-cb-app-8288619
```

Note: you may need to add option `--region=europe-west1` to
the command lines above if the cli is not configured to use
it as the default region. See section "gcloud CLI defaults"
above for more details.

## Cloud Build triggers

```
hiondev setup-github-trigger staging 8288619 xyz
```

Copy example trigger configuration from the hiondev container
image to the project working directory. Alternatively you can
copy the files manually from the hiondev github repository.

```
hiondev cp -r ../eg/cloudbuild /app
```

```
git add cloudbuild
git commit -m 'add cloudbuild triggers'
```

## Memorystore for Redis

```
gcloud beta run integrations create --type=redis \
  --region=europe-north1 --service=app-staging

gcloud run services update app-staging --region=europe-north1
  --update-env-vars=WP_REDIS_DISABLED=false
```

Note! Run `hiondev setup-worker-env staging` after
modifying the app service environment variables or
vpc egress settings. Ie. after the commands above.

## Cloud DNS for custom hion.dev domain names

Create a DNS server for subdomain delegation.
This is not required for the application to work,
but it is required if you want to use the
custom domain for the project.

```bash
DEV_DNS_NAME=xyz.hion.dev
DEV_ZONE_ID=xyz-hion-dev

gcloud dns managed-zones create $DEV_ZONE_ID --description="$DEV_DNS_NAME" --dns-name=$DEV_DNS_NAME --visibility=public --dnssec-state=on

# Get the values for NS and DS records:
gcloud dns managed-zones describe $DEV_ZONE_ID --format="text(nameServers)"
gcloud dns dns-keys list --zone=$DEV_ZONE_ID
gcloud dns dns-keys describe 0 --zone=$DEV_ZONE_ID --format="value(ds_record())"
```

Add this information to the hion.dev root zone
NS (nameservers) and DS (secure delegation) records.
Ask the DNS administrator to do this, or eg.

```bash
gcloud --project=geniem-network dns record-sets create $DEV_DNS_NAME. --type=NS --rrdatas=ns-cloud-c1.googledomains.com.,ns-cloud-c2.googledomains.com.,ns-cloud-c3.googledomains.com.,ns-cloud-c4.googledomains.com. --zone=hion-dev --ttl=300

gcloud --project=geniem-network dns record-sets create $DEV_DNS_NAME. --type=DS --rrdatas="43114 8 2 DA...see above...1D" --zone=hion-dev --ttl=300
```

## Certificate manager & load balancer for custom domain name

Cloud Run integrations could be used to create the load balancer,
but it doesn't create eg. the certificate manager certificates and
ipv6 addresses (at the moment).

```bash
# DO NOT USE THIS
# gcloud beta run integrations create --type=custom-domains \
# --parameters='set-mapping=app-staging.xyz.hion.dev:app-staging'
```

Insted, create the load balancer manually (see below). This is a bit
more work, but it gives more control over the load balancer
configuration and certificates.

### LB backend service

Every Cloud Run service needs to be defined as a backend service
to the load balancer. This will be the default backend service
used for all traffic that does not match any host&path rules.

Backends are then targeted with different host and path rules
that are defined in the url-map below.

```bash
RUN_SERVICE_NAME=app-staging
RUN_REGION=europe-north1
BACKEND_NAME=app-staging

# LB backend service is a Serverless NEG for a specific Cloud Run service
gcloud compute network-endpoint-groups create $RUN_SERVICE_NAME --region=$RUN_REGION --network-endpoint-type=serverless --cloud-run-service=$RUN_SERVICE_NAME
gcloud compute backend-services create $BACKEND_NAME --global --enable-cdn --compression-mode=AUTOMATIC --serve-while-stale=1d --load-balancing-scheme=EXTERNAL_MANAGED
gcloud compute backend-services add-backend $BACKEND_NAME --global --network-endpoint-group=$RUN_SERVICE_NAME --network-endpoint-group-region=$RUN_REGION
```

### LB backend bucket

```bash
GCS_UPLOADS_BUCKET=client-xyz-staging-app-uploads
gcloud compute backend-buckets create $GCS_UPLOADS_BUCKET --gcs-bucket-name=$GCS_UPLOADS_BUCKET --enable-cdn --compression-mode=automatic
```

### LB url-maps and frontends (ipv4 and ipv6)

Url-map creation and frontend definitions (eg. the
ip addresses) are done only once when creating a new LB.
After that, only the url-map rules need to be updated when
adding new services.

```bash
# Empty url-map with default backend and redirect map for http->https
gcloud compute url-maps create app-lb --default-service=app-staging
gcloud compute url-maps import app-lb-redirect --quiet << EOD
name: app-lb-redirect
defaultUrlRedirect:
  httpsRedirect: true
  redirectResponseCode: MOVED_PERMANENTLY_DEFAULT
EOD

# Certificates will added to this empty certificate map
gcloud certificate-manager maps create app-lb-certs

# Target maps for ipv4 and ipv6, with certificates (HTTPS) or redirect (HTTP)
gcloud compute target-https-proxies create app-lb-ipv4 --url-map=app-lb --certificate-map=app-lb-certs
gcloud compute target-https-proxies create app-lb-ipv6 --url-map=app-lb --certificate-map=app-lb-certs
gcloud compute target-http-proxies create app-lb-ipv4 --url-map=app-lb-redirect
gcloud compute target-http-proxies create app-lb-ipv6 --url-map=app-lb-redirect

# Reserve IP addresses and attach them to the target proxies above
gcloud compute addresses create app-lb-ipv4 --ip-version=IPV4 --global
gcloud compute addresses create app-lb-ipv6 --ip-version=IPV6 --global
gcloud compute forwarding-rules create app-lb-ipv4-https --address=app-lb-ipv4 --ports=443 --target-https-proxy=app-lb-ipv4 --load-balancing-scheme=EXTERNAL_MANAGED --global
gcloud compute forwarding-rules create app-lb-ipv6-https --address=app-lb-ipv6 --ports=443 --target-https-proxy=app-lb-ipv6 --load-balancing-scheme=EXTERNAL_MANAGED --global
gcloud compute forwarding-rules create app-lb-ipv4-http --address=app-lb-ipv4 --ports=80 --target-http-proxy=app-lb-ipv4 --global
gcloud compute forwarding-rules create app-lb-ipv6-http --address=app-lb-ipv6 --ports=80 --target-http-proxy=app-lb-ipv6 --global
```

### Url-map path and host rules

Url-map path matchers map eg. `/*` to the Cloud Run
and `/app/uploads/*` to the bucket. Host rules
map given hostnames to a path matcher.

```bash
BACKEND_SERVICE_NAME=app-staging # same as RUN_SERVICE_NAME
BACKEND_BUCKET_NAME=client-xyz-staging-app-uploads # same as GCS_UPLOADS_BUCKET
SERVICE_FQDN=app-staging.xyz.hion.dev

gcloud compute url-maps add-path-matcher app-lb --path-matcher-name=$BACKEND_SERVICE_NAME --default-service=$BACKEND_SERVICE_NAME --backend-bucket-path-rules="/app/uploads/*=$BACKEND_BUCKET_NAME"
gcloud compute url-maps add-host-rule app-lb --hosts="$SERVICE_FQDN" --path-matcher-name=$BACKEND_SERVICE_NAME
```

You can edit the url-maps (YAML) also in a editor with eg.
`gcloud compute url-maps edit app-lb`, or in the console.

### Add certificates

The HTTPS target proxies above are configured to use a certificate
map. This map is empty by default, so we need to add certificates
to it. Certificates are validated with a DNS challenge which is
created withe the `dns-authorizations` command.

```bash
SERVICE_FQDN=app-staging.xyz.hion.dev
SERVICE_CERT_ID=app-staging-xyz-hion-dev

gcloud certificate-manager dns-authorizations create $SERVICE_CERT_ID --domain="$SERVICE_FQDN"
gcloud certificate-manager certificates create $SERVICE_CERT_ID --domains="$SERVICE_FQDN" --dns-authorizations=$SERVICE_CERT_ID
gcloud certificate-manager maps entries create $SERVICE_CERT_ID --map=app-lb-certs --certificates=$SERVICE_CERT_ID --hostname="$SERVICE_FQDN"
```

### Print out all DNS records (optional)

Use these commands to print out all the required
DNS records for certificate validation. These need
to be added to the DNS provider. Same information can
be also found in the console.

Certificates are created automatically after
the DNS validation is visible in the DNS system.

```bash
gcloud certificate-manager dns-authorizations list --format='csv(dnsResourceRecord.name,dnsResourceRecord.type,dnsResourceRecord.data)'

IPV4=$(gcloud compute forwarding-rules describe app-lb-ipv4-https --global --format="value(IPAddress)")
IPV6=$(gcloud compute forwarding-rules describe app-lb-ipv6-https --global --format="value(IPAddress)")

foreach hostname ( `gcloud certificate-manager maps entries list --map=app-lb-certs --format='value(hostname)'` )
  echo "$hostname.,A,$IPV4"
  echo "$hostname.,AAAA,$IPV6"
end
```

Ask the DNS administrator add the records. For internal (hion.dev) addresses
this can be done directly with gcloud CLI (see eg. below), or with the console.

```bash
gcloud dns record-sets create _acme-challenge.app-staging.xyz.hion.dev. --type=CNAME --rrdatas=1d...authorize.certificatemanager.goog. --ttl=300 --zone=xyz-hion-dev
gcloud dns record-sets create app-staging.xyz.hion.dev. --type=A --rrdatas=34.1.2.3 --ttl=300 --zone=xyz-hion-dev
gcloud dns record-sets create app-staging.xyz.hion.dev. --type=AAAA --rrdatas=2600:1...7:: --ttl=300 --zone=xyz-hion-dev
```

### Adding more services to the LB

After deploying more services (eg. Cloud Run app-production)
follow the steps above to add new backend service, backend
bucket, url-map rules and certificates.

The LB will accept https traffic for any hostnames (with a TLS SNI
handshake) that are configured to the certificate map. Then, if one
of the url-map host rules match the given HTTP Host header, the traffic
is routed to the correct path matcher and then to the backend. If the Host
does not match any host rules, the traffic is routed to the url-map's
default backend.

## Running wp search-replace for staging environment

Fetch the environment configuration from the Cloud Run staging
service. This script will create / overwrite the `.env.app.staging`
file.

```
hiondev setup-run-env-download staging
```

Note: this does not fetch possible secrets. If these
are required for the wp-cli commands running locally,
you have to fetch them manually.

```
docker compose build staging
SQL_CONNECTION=... docker compose run --rm staging sh
/app # wp search-replace http://localhost:8080 https://app-staging.xyz.hion.dev
```

Note: The SQL_CONNECTION must be defined. Without it the
Cloud SQL proxy does not connect at all and you
get database connection error in wp-cli. This is a
safety feature to avoid unintentional access to the
live databases.

## Store the dot-env files

When the project setup is complete, save the dot-env files
to the Secret Manager. This is the setup that next developer
should use to continue the project development.

```
hiondev dot-env-store
```

This stores your local files `.env.app`, `.env.app.staging`
and `.env.app.production` to the Secret Manager. The next
developer can then fetch the same files with
`hiondev dot-env-fetch` when setting up a new development
environment, without much knowledge of the project.

## Example: secrets and configs

Follow the projects own README for project specific
setup instructions, for example if there are other files
that need to be fetched from the Secret Manager to get
a local development environment running.

Use Secret Manager to store all such secrets and similar
initial configuration snippets. Do not commit them
to the version control nor save to 1Password or similar.
Note: Remember to `.gitignore` all such files.

```
gcloud secrets create SECRET_NAME
gcloud secrets versions add SECRET_NAME --data-file=filename.txt
```

This can be then documented to the projects own README
as the setup instructions:

```
gcloud secrets versions access latest --secret=SECRET_NAME \
  --out-file=filename.txt
```

## Example: Cloud Run bulk updates and clones

Export the service spec to a file, edit the necessary
sections (eg. env vars) in the file and replace the
service with the updated spec file.

```
gcloud run services describe app-staging --format=export >/tmp/spec.yaml
# edit /tmp/spec.yaml
gcloud run services replace /tmp/spec.yaml
```

No need to remove extra sections (eg. metadata) from
the spec file. This is also easiest way to clone a
service to a new name by changing the name in the
spec file.

Some `yq` examples for manipulating the spec and dot-env files:

```
# Convert dot-env file to a YAML Mapping:
cat .env.app | yq -p props -o yaml

# Convert dot-env file to env section of a spec:
cat .env.app | yq -p props -o yaml 'to_entries | map({"name":.key,"value":.value}) | filter(.value) | unique_by(.name)' >/tmp/spec-env.yaml

# Replace env section of a spec with env section from a file:
yq -i eval-all 'select(fileIndex==0).spec.template.spec.containers[0].env = select(fileIndex==1) | select(fileIndex==0)' /tmp/spec.yaml /tmp/spec-env.yaml

# Replace env section of a spec with env section from another spec:
yq -i eval-all 'select(fileIndex==0).spec.template.spec.containers[0].env = select(fileIndex==1).spec.template.spec.containers[0].env | select(fileIndex==0)' /tmp/spec.yaml /tmp/another-spec.yaml

# Convert env section of a spec to a YAML Mapping:
yq '.spec.template.spec.containers[0].env | filter(.value) | map({"key":.name,"value":.value}) | from_entries' /tmp/spec.yaml

# Convert env section of a spec to a simple dot-env file:
yq '.spec.template.spec.containers[0].env | filter(.value) | map([.name,.value] | join("=")) | join("\n")' /tmp/spec.yaml

# Note: with a newer yq version you can use "shell" output
# format. This handles quoting and escaping better:
yq -o shell '.spec.template.spec.containers[0].env | filter(.value) | map({"key":.name,"value":.value}) | from_entries' /tmp/spec.yaml
```

To get servive environment variables in shell format. Note, this
does not include the secrets, only the plain environment variables:

```
gcloud run services describe app-staging --format=export | yq -o shell '.spec.template.spec.containers[0].env | filter(.value) | map({"key":.name,"value":.value}) | from_entries'
```

## OPTION: Create SendGrid key for emails

1. Search _SendGrid_ in Google Cloud Console and select SendGrid Email API under Marketplace section.
2. Click _Manage on Provider_ (rest of the steps are done in SendGrid dashboard).
3. Go to _Settings_ > _Subuser management_ and click _Create new subuser_.
4. Add username (f. ex. client-hamina), email (does not really matter, use f. ex. your own email with _+client-{client}_ suffix) and password (doesn't matter either).
5. Select the dedicated IP address from the list and create subuser, rest of the options can be left empty.
6. Select _Change account_ under the user dropdown (top left).
7. Select the user you just created to switch to it.
8. Click _Create API key_ and give it a name.
9. Select _Restricted Access_ and check section _Mail Send > Mail Send_ so that only that is enabled.
10. Copy the API key shown, it can't be viewed again.
11. Go to _Settings_ > _Sender Authentication_ and click _Get started_ under _Domain authentication_.
12. Select _DNS Host: Google Cloud_ and give a From Domain (f. ex. `{client here}.hion.dev`.
13. Go to Cloud DNS in Google Console and add the listed DNS records to the project.
14. Click _Verify_ in SendGrid dashboard.

### Add the environment variables

Create a secret `SENDGRID_API_KEY` and set the API key as its value.

```
gcloud secrets create SENDGRID_API_KEY
printf "{api key here}" | gcloud secrets versions add SENDGRID_API_KEY --data-file=-
```

Add the secret to both environments as secret references. Also set `PHPMAILER_FROM` env variable to a suitable value.
