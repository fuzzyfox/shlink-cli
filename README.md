# shlink-cli

An unofficial [Shlink](https://shlink.io/) cli rest client for creating/managing
a Shlink server via its API, and the command line.

## Usage

From clipboard on macOS

```sh
$ pbpaste | shlink-cli short-url:generate | pbcopy

$ shlink-cli short-url:generate   

 URL to shorten?:
 > https://github.com/fuzzyfox/shlink-cli 

https://doma.in/xQspJ

$ shlink-cli short-url:parse https://doma.in/xQspJ
https://github.com/fuzzyfox/shlink-cli

$ shlink-cli short-url:parse xQspJ
https://github.com/fuzzyfox/shlink-cli
```

---
This project is build on top of [laravel-zero](https://github.com/laravel-zero/laravel-zero).
