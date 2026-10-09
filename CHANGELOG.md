### 1.2.0
- Fix: `bedrock:env` copies the `.env` of the live release, then of the newest release that has one. An aborted deploy no longer makes it generate a new `.env`.
- Fix: `bedrock:env` refuses to generate a `.env` without an interactive terminal instead of writing default answers (empty DB password).
- Fix: generated `.env` values are quoted for phpdotenv and written via base64, so salts with `$[`, backticks or quotes no longer break the deploy, and the values stay out of the output.
- Add: a failed `update` runs `deploy:failed` as well. A failed deploy removes the release it started and releases the lock it acquired.

### 1.0.6
- Fix: Download Filetransfer

### 1.0.5
- Fix: Package Paths

### 1.0.4
- Renamed repository to bedrock-deployer-7
- Merged FlorianMoser v1.15.0 to include sage recipe (untested)
- Fix exclude syntax for push:files-no-bak & pull:files-no-bak

### 1.0.3
- Fix: trellis vm / Lima Support

### 1.0.2
- Add: trellis vm / Lima Support

### 1.0.1