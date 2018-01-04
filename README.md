# Blast Search Bundle

Bundle that handle search indexes with an ElasticSearch instance.

## Requirements

You must have an ElasticSearch running and properly configured

## (OPTIONNAL) Using an Elastic Stack stack

### Using distribution

```bash
sudo apt-get install elasticsearch
```

Configure your elastic instance to listen to the right network interface by editing `/etc/elasticsearch/elasticsearch.yml` :

```yaml
network.host: 0.0.0.0 # change 0.0.0.0 with your public IP or 127.0.0.1 for local exposure only
http.port: 9200
```

### Using Docker

For development purposes only, you can use a complete Elastic Stack (previouly ELK stack).

For simplicity, you can use a Docker image for that stack : https://hub.docker.com/r/sebp/elk/

```bash
docker run -p 5601:5601 -p 9200:9200 -p 5044:5044 -it --name elk sebp/elk:563
```

_Note :_ Don't use ElasticSearch 6 because of a incompatibility between ES 6 and FOSElasticaBundle (see this issue https://github.com/FriendsOfSymfony/FOSElasticaBundle/issues/1267)

You can launch this Docker image at stratup with a custom SystemD service (create the service file `/etc/systemd/system/elk.service`) :

```bash
[Unit]
Description=Start Elastic Stack at startup
After=network.target

[Service]
ExecStart=/usr/bin/docker container start elk

Type=forking

[Install]
WantedBy=default.target
```

```bash
sudo systemctl enable elk
```

## Map your entities with the search index

In a config file, add this content

```yml
fos_elastica:
    clients:
        default:
            host: localhost
            port: 9200
    indexes:
        global:
            use_alias: true
            index_name: %blast_search.global_index_name%
            types:

                # Below is an example about how to map an entity to a search index

                user:
                    properties:
                        username: ~
                        email: ~
                    persistence:
                        driver: orm
                        model: "%sil.model.user.class%"
                        listener: ~ # by default, listens to "insert", "update" and "delete"
```

## Populate indexes

Run the FOSElasticaBundle command `bin/console fos:elastica:populate` to init elastic search indexes.
