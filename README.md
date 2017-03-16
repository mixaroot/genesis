# инструкция 

1) Запуск воркера

php index.php WorkerAlbumsPhotosConsole writeMethod

2) Записать данные(информацию о пользователе, альбомах, картинках) по id

php index.php WriteAlbumsPhotosConsole writeMethod --id=144623167

3) Записать данные(информацию о пользователях, альбомах, картинках) по id, с csv файла

php index.php WriteAlbumsPhotosConsole writeMethod --path=D:\users.csv

3) Записать данные(информацию о пользователях, альбомах, картинках) по id, в консоль нужно передать путь к файлу или id

php index.php WriteAlbumsPhotosConsole writeMethod

4) Чтение информации о пользователе (имя, фамилия, картика 75 на 75)

php index.php ReadAlbumsPhotosConsole readMethod --id=144623167

# Замечания
Получать картинки и альбомы можно кусками с помощью параметра offset (https://vk.com/dev/photos.getAlbums, https://vk.com/dev/photos.get).
По причине того что у пользователя может быть очень много картинок.
Но решил что для тестового задания достаточно и без offset.

Также получения объектов можно вынести в отдельные методы чтобы было проще мокать, или применить внедрение зависимостей, но решил что для тестового задания достаточно.
