## 👋 Welcome to tor 🚀  

tor README  
  
  
## Run container

```shell
dockermgr update tor
```

### via command line

```shell
docker pull casjaysdevdocker/tor:latest && \
docker run -d \
--restart always \
--name casjaysdevdocker-tor \
--hostname casjaysdev-tor \
-e TZ=${TIMEZONE:-America/New_York} \
-v $HOME/.local/share/srv/docker/tor/files/data:/data:z \
-v $HOME/.local/share/srv/docker/tor/files/config:/config:z \
-p 80:80 \
casjaysdevdocker/tor:latest
```

### via docker-compose

```yaml
version: "2"
services:
  tor:
    image: casjaysdevdocker/tor
    container_name: tor
    environment:
      - TZ=America/New_York
      - HOSTNAME=casjaysdev-tor
    volumes:
      - $HOME/.local/share/srv/docker/tor/files/data:/data:z
      - $HOME/.local/share/srv/docker/tor/files/config:/config:z
    ports:
      - 80:80
    restart: always
```

## Authors  

🤖 casjay: [Github](https://github.com/casjay) [Docker](https://hub.docker.com/r/casjay) 🤖  
⛵ CasjaysDevDocker: [Github](https://github.com/casjaysdevdocker) [Docker](https://hub.docker.com/r/casjaysdevdocker) ⛵  
