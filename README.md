# statusUpdate
Обновление товаров в интернет-магазине
Этот скрипт предназначен для обновления информации о товарах в интернет-магазине на основе определенных правил и условий. Он поддерживает следующие операции:

Обновление товара по его ID.
Обновление информации о всех товарах в магазине.
Обновление товаров по бренду.
Очистка статуса "популярный" для всех товаров.
Очистка статуса "новый" для всех товаров.
Использование
Для использования скрипта, убедитесь, что у вас установлен PHP и настроены необходимые зависимости. Затем, выполните соответствующий запрос к этому скрипту.

Обновление товара по ID
Отправьте POST запрос на скрипт с параметром productId, содержащим ID товара, который необходимо обновить.

Обновление информации о всех товарах
Отправьте POST запрос на скрипт с параметром updateAll. Это обновит информацию о всех товарах в магазине согласно заданным правилам.

Обновление товаров по бренду
Отправьте POST запрос на скрипт с параметром vendorId, содержащим ID бренда товаров, которые требуется обновить.

Очистка статуса "популярный" для всех товаров
Отправьте POST запрос на скрипт с параметром clearPopular. Это удалит статус "популярный" у всех товаров.

Очистка статуса "новый" для всех товаров
Отправьте POST запрос на скрипт с параметром clearNew. Это удалит статус "новый" у всех товаров.

Логирование и отчетность
Скрипт также ведет журнал событий, который сохраняется в файл update_log.txt. По завершении всех операций, создается отчет, сохраняемый в файл update_report.txt.

Ошибки и уведомления
В случае возникновения ошибок или успешного завершения операций, скрипт отправляет соответствующие уведомления.

Заметки
Убедитесь, что пути к файлам конфигурации и зависимостям указаны верно.
Проверьте соответствие правилам обновления товаров в вашем магазине перед использованием скрипта.
Для получения подробной информации о настройке и использовании скрипта, обратитесь к создателю.