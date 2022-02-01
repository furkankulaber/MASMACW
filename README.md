# Mobile Application Subscription Managment API / Callback / Worker

### Run Command :
````
docker-compose up -d
````

### Work:
````
API / Worker / Callback
````

### Worker / Callback:
>Copies of each other are working by 3 consumers and they are working as 2 each. A total of 6 consumers work.

#### Note:
>The system cannot process millions of data in its current state. In order to process millions of data, entity connection must be broken in Doctrine queries and all sql queries must be in raw sql format and their returns must be processed without entity inheritance. Doctrine Entity collection slows down the system because it connects more than one object while the system is loading.


#### Apache Jmeter Test as an example:
```
Ptest.jmx file
```

### Used for the system:
``` 
PHP-FPM / Nginx / Mysql / RabbitMQ
```

### Postman collection for testing:
```
MASMACW.postman_collection.json file
```


# All data will be deleted every time the system is turned off and on!!!
