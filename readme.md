Задание

- реализуем поиск по книжному интернет-магазину с помощью Elasticsearch
- у каждого товара есть название, категория, цена и кол-во остатков на складе
- поиск должен корректно работать с опечатками и русской морфологией
- пример: пользователь ищет все исторические романы дешевле 2000 рублей (и в наличии) по поисковому запросу "рыцОри"
- в результате должны вернуться товары, ранжированные по релевантности
- домашку нужно сдать как консольное PHP-приложение, которое принимает один или несколько параметров командной строки и выводит результат в виде текстовой таблички, после чего завершает работу
- JSON с товарами будет приложен к занятию в ЛК
- способ создания индекса и его первоначального заполнения — на ваш выбор

Как пользоваться приложением:
- Запускаем сборку docker-compose окружения
```bash
./docker-build.sh
```
- Чтобы запустить консольное приложение:
```bash
./run-application.sh
```