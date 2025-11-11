
# Command to enter docker container
container:
    docker exec -it app-php-1 bash

# Command to build TypeScript files (watch mode)
build-ts:
    php bin/console typescript:build --watch

