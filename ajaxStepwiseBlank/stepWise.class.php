<?php
/**
 * User: Petja
 * Date: 31.03.13
 * Time: 12:50
 */

class stepWise {

    public function DbRecreateWithQueue(){

        // Берет очередь задач. Например, список картинок к обработке или список ссылок, чтобы добавить их в SAPE. У меня здесь просто массив.
        $tasks = array(
            'Задача 1',
            'Задача 2',
            'Задача 3',
            'Задача 4',
            'Задача 5',
            'Задача 6',
            'Задача 7',
            'Задача 8',
            'Задача 9',
            'Задача 10'
        );

        try {
            // Открыть БД SQLite или создать, если ее нет
            $db = new PDO('sqlite:temp.db');
            $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // Очищает таблицу
            $db->exec(
                "DROP TABLE IF EXISTS tempTableForTasks;"
            );

            // Создает таблицу для очереди
            $db->exec(
                "CREATE TABLE IF NOT EXISTS tempTableForTasks(
                    id INTEGER PRIMARY KEY,
                    tasks TEXT,
                    status TEXT,
                    optional TEXT
                );"
            );

            // Помещает очередь в БД
            foreach($tasks as $task){
                static $n = 1;
                $db->exec("INSERT INTO tempTableForTasks(tasks, status, optional) VALUES ('".$task."', '', 'Запись № ".$n."');");
                $n++;
            }

            // Закрываем подключение к БД
            $db = null;

            // Возвращаем данные в случае успеха
            return array(
                'status' => 'success',
                'type' => 'message',
                'method' => __METHOD__,
                'message' => 'БД успешно создана!'
            );
        }
        catch(PDOException $e) {
            // Возвращаем данные в случае ошибки
            return array(
                'status' => 'error',
                'type' => 'message',
                'method' => __METHOD__,
                'message' => $e->getMessage()
            );
        }
    }

    public function DbShowData(){
        try {
            // Открыть БД SQLite или создать, если ее нет
            $db = new PDO('sqlite:temp.db');
            $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // Взять данные из таблицы
            $result = $db->query('SELECT * FROM tempTableForTasks');
            $result_all = $result->fetchall(PDO::FETCH_ASSOC);

            // Формируются заголовки таблицы из ключей
            $output = '<table><tr>';
            foreach(array_keys($result_all[0]) as $key){
                $output .= "<th>".$key."</th>";
            }
            $output .= '</tr>';

            // Формируется сама таблица
            foreach($result_all as $tr){
                $output .= '<tr>';
                foreach($tr as $td){
                    $output .= '<td>'.$td.'</td>';
                }
                $output .= '</tr>';
            }
            $output .= '</table>';

            // Закрываем подключение к БД
            $db = null;

            // Возвращаем данные в случае успеха
            return array(
                'status' => 'success',
                'type' => 'message',
                'method' => __METHOD__,
                'message' => $output
            );
        }
        catch(PDOException $e) {
            // Возвращаем данные в случае ошибки
            return array(
                'status' => 'error',
                'type' => 'message',
                'method' => __METHOD__,
                'message' => $e->getMessage()
            );
        }
    }

    /*
     * Взять из БД один не обработанный элемент
     * Обработать его
     * Вернуть успех или ошибку
     * Пометить в БД этот элемент как обработанный
     * */
    public function ProcessQueue(){

        try {
            // Открыть БД SQLite или создать, если ее нет
            $db = new PDO('sqlite:temp.db');
            $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // Взять одну не обработанную строку из таблицы
            $result = $db->query('SELECT * FROM tempTableForTasks WHERE status != "processed"');
            $notProcessedColumn = $result->fetch(PDO::FETCH_ASSOC);
            // Если необработанных строк не осталось, возвращаем status complete
            if(!$notProcessedColumn){
                return array(
                    'status' => 'complete',
                    'type' => 'queue',
                    'method' => __METHOD__,
                    'message' => 'Конец'
                );
            }

            // Отправить задание на обработку
            $status = $this->OneAction();
            if($status == 'success'){
                $db->exec('UPDATE tempTableForTasks SET status="processed", optional="обработано" WHERE id = "'.$notProcessedColumn['id'].'"');
            }elseif($status == 'error'){
                $db->exec('UPDATE tempTableForTasks SET status="processed", optional="ошибка" WHERE id = "'.$notProcessedColumn['id'].'"');
            }

            // Формируем строку ответа - получившаяся строка в БД после обработки
            $result = $db->query('SELECT * FROM tempTableForTasks WHERE id = "'.$notProcessedColumn['id'].'"');
            $processedColumn = $result->fetch(PDO::FETCH_ASSOC);

            // Закрываем подключение к БД
            $db = null;

            // Возвращаем данные в случае успеха
            return array(
                'status' => $status,
                'type' => 'queue',
                'method' => __METHOD__,
                'message' => implode(", ", $processedColumn)."<br>\n"
            );
        }
        catch(PDOException $e) {
            // Возвращаем данные в случае ошибки
            return array(
                'status' => 'error',
                'type' => 'message',
                'method' => __METHOD__,
                'message' => $e->getMessage()
            );
        }
    }

    /*
     * Это демонстрационная функция, выполняется с задержкой в 1 секунду
     * и возвращает ошибку с вероятность 30%
     * */
    private function OneAction(){
        // Задержка на секунду для видимости обработки
        sleep(1);
        // С вероятностью 30% возвращается ошибка
        if (rand(1, 100) <= 30){
            return 'error';
        }
        return 'success';
    }
}
