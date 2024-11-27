#!/usr/bin/env sh

SURREAL_VERSION=${1:-v2}
WORKING_DIR=$(dirname "$0")
CONTAINER_NAME="surrealdb-laravel-e2e"

echo " "
echo "Configuring SurrealDB container"
echo "-----------------------------------------"
echo "WORKING_DIR=$WORKING_DIR"
echo "CONTAINER_NAME=$CONTAINER_NAME"
echo "-----------------------------------------"
echo " "

echo "Checking if docker daemon is running"

if (! docker stats --no-stream ); then
  echo "Please start the docker daemon"
  exit 1
fi

echo "Checking for current docker container"

if docker ps --filter "name=$CONTAINER_NAME" | grep $CONTAINER_NAME; then
    echo "Container already running, stopping container..."
    docker container stop $CONTAINER_NAME
fi

if docker container ls -a --filter "name=$CONTAINER_NAME" | grep $CONTAINER_NAME; then
    echo "Container already exists, removing container..."
    docker container rm $CONTAINER_NAME
fi

echo "Starting container..."
docker container run -d --name $CONTAINER_NAME -p 8000:8000 "surrealdb/surrealdb:${SURREAL_VERSION}" start --user root --pass root

echo "Waiting for container to start..."
echo "-----------------------------------------"
sleep 5

echo "Checking logs to see if its started..."
LOGS=$(docker container logs $CONTAINER_NAME)
echo "${LOGS}"

if (echo "${LOGS}" | grep 'Started web server'); then
  echo "SurrealDB failed to start, please fix the errors above!"
  exit 1
fi
