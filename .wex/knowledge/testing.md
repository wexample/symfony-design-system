Test coverage cheatsheet (run from host with Docker)
---------------------------------------------------

ok
> apk add --no-cache php82-pecl-xdebug

ok
apk add --no-cache \
--repository=https://dl-cdn.alpinelinux.org/alpine/edge/main \
--repository=https://dl-cdn.alpinelinux.org/alpine/edge/community \
php83 php83-dev php83-pear php83-xml php83-pecl-xdebug

---

Prereqs
- Inside the container, PHP must have a coverage engine (Xdebug or pcov). Check: `docker exec -ti responsite_local_symfony php -m | grep -E 'xdebug|pcov'`.
- With Xdebug, enable coverage per run: prefix commands with `XDEBUG_MODE=coverage`. With pcov, set `PCOV_ENABLED=1`.

Run tests with coverage (container)
- Text report: `docker exec -ti responsite_local_symfony bash -c "cd /var/www/html/vendor/wexample/symfony-design-system && XDEBUG_MODE=coverage vendor/bin/phpunit tests/ --coverage-text"`
- HTML report: `docker exec -ti responsite_local_symfony bash -c "cd /var/www/html/vendor/wexample/symfony-design-system && XDEBUG_MODE=coverage vendor/bin/phpunit tests/ --coverage-html var/coverage"`
  - Open `var/coverage/index.html` to inspect.
- Clover (CI): `docker exec -ti responsite_local_symfony bash -c "cd /var/www/html/vendor/wexample/symfony-design-system && XDEBUG_MODE=coverage vendor/bin/phpunit tests/ --coverage-clover var/coverage/clover.xml"`

Notes
- The Docker daemon must be accessible from the host to run `docker exec`.
- If coverage flags are ignored, the PHP build in the container likely lacks Xdebug/pcov or the engine is disabled. Install/enable it, then rerun with the commands above.
