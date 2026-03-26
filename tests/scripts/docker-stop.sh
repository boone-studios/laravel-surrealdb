WORKING_DIR=$(dirname "$0")
CONTAINER_NAME="surrealdb-laravel-e2e"

echo " "
echo "Stopping SurrealDB container"
echo "-----------------------------------------"
echo "WORKING_DIR=$WORKING_DIR"
echo "CONTAINER_NAME=$CONTAINER_NAME"
echo "-----------------------------------------"
echo " "

docker stop "$CONTAINER_NAME" || true
echo "Removing container"
docker rm "$CONTAINER_NAME" || true

echo " "
echo "Container is down!"
