# Tool images, only for initial project setup

    PROJECT_ID=...
    gcloud config set project $PROJECT_ID

    PROJECT_NUM=$(gcloud projects describe $PROJECT_ID \
    --format="value(projectNumber)")

# GitHub commit status updater

Build the `github-status-updater` image:

    cd /tmp
    gcloud artifacts repositories create build --location=europe --repository-format=docker
    git clone https://github.com/cloudposse/github-status-updater.git
    cd github-status-updater
    gcloud builds submit --tag europe-docker.pkg.dev/$PROJECT_ID/build/github-status-updater .
    cd -

Generate GitHub Personal Access token and store it to Secret Manager
secret `PR_PREVIEW_STATUS_GITHUB_TOKEN`:

    open https://github.com/settings/tokens
    # select scopes "repo:status" and "public_repo"

    printf "github_pat_..." | gcloud secrets create \
    PR_PREVIEW_STATUS_GITHUB_TOKEN --data-file=-

Allow Cloud Build to access this secret.

    gcloud secrets add-iam-policy-binding PR_PREVIEW_STATUS_GITHUB_TOKEN \
    --member="serviceAccount:$PROJECT_NUM@cloudbuild.gserviceaccount.com" \
    --role=roles/secretmanager.secretAccessor

This image and secret are used in `cloudbuild/*.yaml` build steps.

# Cloud Build service account deploy permissions

For services, run.services.create and run.services.update on
the project level are required. run.services.get is not strictly
required, but is recommended in order to read the status of the service.
Typically assigned through the roles/run.admin role.

```
gcloud projects add-iam-policy-binding $PROJECT_ID \
--member="serviceAccount:$PROJECT_NUM@cloudbuild.gserviceaccount.com" \
--role=roles/run.admin
```

# Cloud Build service account actAs permissions

When a container is deployed to a Cloud Run service, it runs with the
identity of the Runtime Service Account of this Cloud Run service.
Because Cloud Build can deploy new containers automatically, Cloud Build
needs to be able to _act as_ the Runtime Service Account of your Cloud Run service.

To grant limited access to Cloud Build to deploy to a Cloud Run service, eg:

```
gcloud iam service-accounts add-iam-policy-binding \
app-staging-run@$PROJECT_ID.iam.gserviceaccount.com \
--member="serviceAccount:$PROJECT_NUM@cloudbuild.gserviceaccount.com" \
--role="roles/iam.serviceAccountUser"

gcloud iam service-accounts add-iam-policy-binding \
app-production-run@$PROJECT_ID.iam.gserviceaccount.com \
--member="serviceAccount:$PROJECT_NUM@cloudbuild.gserviceaccount.com" \
--role="roles/iam.serviceAccountUser"
```

# Export triggers

```
gcloud beta builds triggers export deploy-pr-staging \
--destination=triggers/deploy-pr-staging.yaml
gcloud beta builds triggers export deploy-pr-main \
--destination=triggers/deploy-pr-main.yaml
gcloud beta builds triggers export deploy-tag-staging \
--destination=triggers/deploy-tag-staging.yaml
gcloud beta builds triggers export deploy-tag-production \
--destination=triggers/deploy-tag-production.yaml
```

# Import triggers

```
gcloud beta builds triggers import --source=triggers/deploy-pr-staging.yaml
gcloud beta builds triggers import --source=triggers/deploy-pr-main.yaml
gcloud beta builds triggers import --source=triggers/deploy-tag-staging.yaml
gcloud beta builds triggers import --source=triggers/deploy-tag-production.yaml
```
