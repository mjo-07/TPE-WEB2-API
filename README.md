# API REST - Sistema de Videojuegos y Editores 🎮

Este proyecto consiste en una API REST nativa desarrollada en PHP que permite gestionar un catálogo de videojuegos y sus empresas editoras. Implementa operaciones CRUD completas, validaciones de integridad referencial en la base de datos, y herramientas avanzadas de filtrado, ordenamiento y paginación dinámicos.

## 📋 Documentación de Endpoints

### 🏢 Sección: Editores (`editors`)

| Método | Endpoint | Descripción | Estado Exitoso |
| :--- | :--- | :--- | :--- |
| **GET** | `/editors` | Obtiene la lista de todos los editores (Soporta Filtros, Orden y Paginación) | `200 OK` |
| **GET** | `/editors/:id` | Obtiene el detalle de un editor específico por su ID | `200 OK` |
| **POST** | `/editors` | Crea un nuevo editor en el sistema | `201 Created` |
| **PUT** | `/editors/:id` | Actualiza de manera total los datos de un editor existente | `200 OK` |
| **DELETE** | `/editors/:id`| Elimina un editor del sistema si no tiene restricciones | `204 No Content` |

#### 📥 Ejemplo de Body para POST / PUT (`editors`)
```json
{
  "nombre_empresa": "Nintendo",
  "pais": "Japon",
  "sitio_web": "[https://nintendo.com](https://nintendo.com)",
  "valoracion": 5,
  "descripcion": "Compañía histórica de videojuegos",
  "imagen": "NULL" //para pruebas se obvió la carga de la imagen
}
```

#### 📋 Especificación de Campos (`editors`)
* **Campos Obligatorios:** `nombre_empresa`, `pais`, `sitio_web`. Si alguno de estos campos falta o está vacío en el JSON enviado, la API interrumpe el flujo y responde inmediatamente con un código `400 Bad Request`.
* **Campos Opcionales:** `descripcion`, `valoracion`, `imagen`. Estos campos permiten valores nulos (`NULL`) en la base de datos. Si el cliente no los envía en el JSON, el controlador les asigna `null` automáticamente antes de procesar la consulta.

---

### 🕹️ Sección: Videojuegos (`games`)

| Método | Endpoint | Descripción | Estado Exitoso |
| :--- | :--- | :--- | :--- |
| **GET** | `/games` | Obtiene la lista de todos los juegos (Soporta Filtros, Orden y Paginación) | `200 OK` |
| **GET** | `/games/:id` | Obtiene el detalle de un videojuego específico (Incluye datos de su Editor) | `200 OK` |
| **POST** | `/games` | Crea un nuevo videojuego en el catálogo | `201 Created` |
| **PUT** | `/games/:id` | Actualiza de manera total los datos de un videojuego existente | `200 OK` |
| **DELETE** | `/games/:id` | Elimina un videojuego del catálogo de forma permanente | `204 No Content` |

#### 📥 Ejemplo de Body para POST / PUT (`games`)
```json
{
  "titulo": "The Legend of Zelda: Breath of the Wild",
  "precio": 59.99,
  "fecha_lanzamiento": "2017-03-03",
  "id_editor": 1,
  "descripcion": "Un juego de aventura de mundo abierto.",
  "resenia": "Una obra maestra de la industria.",
  "imagen": "NULL" //para pruebas se obvió la carga de la imagen
}
```

#### 📋 Especificación de Campos (`games`)
* **Campos Obligatorios:** `titulo`, `precio`, `fecha_lanzamiento`, `id_editor`. Si alguno falta en el JSON, la API devuelve un `400 Bad Request`. Adicionalmente, el campo `id_editor` se valida contra la base de datos: si el ID provisto no pertenece a ningún editor existente, la API frena la inserción y arroja un `400 Bad Request` informando el error de clave foránea.
* **Campos Opcionales:** `descripcion`, `resenia`, `imagen`. Estos campos aceptan valores vacíos o nulos (`NULL`), flexibilizando la carga o actualización de la ficha técnica del videojuego.

---

## ⚙️ Parámetros de Query Disponibles (Endpoints de Listas)

Los endpoints de colecciones (`GET /editors` y `GET /games`) aceptan parámetros de consulta dinámicos (*Query Params*) en la URL para procesar las búsquedas en el servidor:

### 1. Filtrado Dinámico (`filtrar_por` & `valor`)
Para activar el filtro, es obligatorio enviar ambos parámetros en simultáneo.
* **Filtros válidos en Editores:** `nombre_empresa`, `pais`, `valoracion`.
* **Filtros válidos en Videojuegos:** `titulo`, `precio`, `valoracion`, `fecha_lanzamiento`, `id_editor`.
* **Lógica de comparación:** Si el campo es de texto (`nombre_empresa`, `pais`, `titulo`), el modelo aplica una búsqueda parcial de coincidencias mediante `LIKE %valor%`. Si el campo es numérico o un ID (`precio`, `id_editor`, `valoracion`), se aplica una coincidencia exacta (`= valor`).

### 2. Ordenamiento Dinámico (`order_by` & `order_dir`)
Permite ordenar los registros devueltos por cualquier columna mediante una lista blanca de seguridad en el controlador. El sentido se define con `order_dir` siendo `ASC` (por defecto) o `DESC`.
* **Columnas permitidas en Editores:** `id_editor`, `nombre_empresa`, `descripcion`, `pais`, `sitio_web`, `valoracion`.
* **Columnas permitidas en Videojuegos:** `id_videojuego`, `titulo`, `descripcion`, `precio`, `valoracion`, `fecha_lanzamiento`, `id_editor`.

### 3. Paginación Blindada (`page` & `limit`)
Divide los resultados del listado en fragmentos o páginas controladas mediante `page` (número de página) y `limit` (elementos por página).
* **Blindaje contra fallos:** Si el consumidor de la API ingresa texto inválido, un número negativo o un valor de `0`, los controladores sanitizan las variables forzando valores seguros por defecto (`page = 1` y `limit = 5`) asegurando que la consulta de base de datos nunca se rompa.

---

### 🚀 Ejemplos de Uso

A continuación, se detallan ejemplos reales de consultas combinadas listas para testear:

* **Filtrar Editores por País (Parcial):** Trae los editores radicados en Japón.
  ```text
  GET http://localhost/tu_proyecto/editors?filtrar_por=pais&valor=Japon
  ```

* **Filtrar Juegos por Editor (Exacto):** Trae solo los juegos pertenecientes al editor con ID 2.
  ```text
  GET http://localhost/tu_proyecto/games?filtrar_por=id_editor&valor=2
  ```

* **Filtrar Juegos por Título (Parcial):** Busca cualquier juego que contenga la palabra "Zelda".
  ```text
  GET http://localhost/tu_proyecto/games?filtrar_por=titulo&valor=Zelda
  ```

* **Ordenar Juegos por Precio:** Lista todos los videojuegos ordenados del más caro al más barato.
  ```text
  GET http://localhost/tu_proyecto/games?order_by=precio&order_dir=DESC
  ```

* **Paginación Pura de Editores:** Trae una sección reducida de los primeros 3 editores de la lista.
  ```text
  GET http://localhost/tu_proyecto/editors?page=1&limit=3
  ```

* **Query Combinada Completa (Videojuegos):** Filtra los videojuegos del editor 1, ordenados alfabéticamente por su título de forma ascendente, mostrando los primeros 5 resultados de la página 1.
  ```text
  GET http://localhost/tu_proyecto/games?filtrar_por=id_editor&valor=1&order_by=titulo&order_dir=ASC&page=1&limit=5
  ```

---

## ⚠️ Códigos de Error Manejados
El sistema responde de manera consistente utilizando códigos de estado HTTP estandarizados acompañados de un mensaje aclaratorio en formato JSON:

* **`400 Bad Request`**: Se emite si faltan campos obligatorios en el cuerpo del JSON enviado (`POST` / `PUT`), si se intentan utilizar campos de filtrado u ordenamiento fuera de las listas blancas permitidas, o si se intenta asociar un juego a un `id_editor` inexistente. En la sección de editores, este código también se arroja si se intenta eliminar un editor que posee videojuegos asociados, atrapando el código de error `1451` de restricción de clave foránea de MariaDB.
* **`404 Not Found`**: Se devuelve si el parámetro `:id` solicitado en las rutas de consulta individual (`GET`), modificación (`PUT`) o eliminación (`DELETE`) no coincide con ningún registro físico en la base de datos.