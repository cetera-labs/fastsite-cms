# Обёртка над dev/dev.sh для тех, у кого есть make (Linux, WSL). См. dev/README.md.
.PHONY: up down reset build install test e2e bash mysql logs ps

up down reset build install test e2e bash mysql logs ps:
	sh dev/dev.sh $@
