
# BJJ App

A video app designed by and for BJJ practitioners


## Installation

First clone the project

```bash
  git clone git@github.com:Jorek57/symfony_bjj.git
```

Install the project with composer

```bash
  cd bjj_symfony
  composer install
```

Don't forget to fill the .env with your DB datas.
Once it's done, you can run your migrations with:

```bash
  php bin/console doctrine:migrations:migrate
```

It's all set! Now you can run the app with:

```bash
  symfony server:start
```
And in another terminal, you can run this npm command to build webpack

```bash
  npm run watch
```

The project is now available on your localhost:8000
