# Guía de Instalación y Ejecución del Proyecto

Sigue los pasos detallados a continuación para configurar y poner en marcha el entorno de desarrollo completo.

---

## 1. Base de Datos

Inicializa la base de datos importando el archivo SQL provisto en la terminal:

```bash
mysql -u root -p < db/init.sql

```

---

## 2. Configuración y Ejecución del Backend

1. Entra a la carpeta del backend:

```bash
cd backend

```

2. Instala las dependencias de PHP:

```bash
composer install

```

3. Crea tu archivo de entorno a partir del ejemplo y configura las credenciales con permisos para modificar la base de datos:

```bash
cp .env.example .env

```

_(Asegúrate de configurar correctamente el usuario y contraseña dentro de tu archivo `.env`)._

> Si necesitas crear un usuario de base de datos específico para el proyecto, puedes ejecutar lo siguiente en tu consola de MySQL:
>
> ```sql
> CREATE USER IF NOT EXISTS 'user_licitaciones'@'localhost' IDENTIFIED BY 'modulo123';
> GRANT ALL PRIVILEGES ON modulo_licitaciones.* TO 'user_licitaciones'@'localhost';
> FLUSH PRIVILEGES;
>
> ```

4. Importa las actividades iniciales:

```bash
composer import:activities

```

5. Verifica que tengas habilitada la extensión **ZIP** (necesaria para la exportación del listado):

```bash
php -m | grep -i zip

```

> Si no la tienes instalada en tu sistema, instálala con:
>
> ```bash
> sudo apt install php-zip
>
> ```

6. Levanta el servicio del backend:

```bash
composer start

```

_(Nota: Si el puerto se queda bloqueado en segundo plano, puedes liberarlo ejecutando)_:

```bash
sudo fuser -k 8001/tcp

```

---

## 3. Configuración y Ejecución del Frontend

1. Dirígete a la carpeta del frontend:

```bash
cd frontend

```

2. Crea tu archivo de entorno basado en el ejemplo:

```bash
cp .env.example .env

```

_(En este archivo `.env` debes especificar la URL con la que se levantó la API del backend, que por lo general corre en el puerto `8001` de localhost)._

3. Instala las dependencias de Node:

```bash
npm install

```

4. Inicia el servidor de desarrollo del frontend:

```bash
npm run dev

```

5. Una vez iniciado el servicio puedes ingresar a la URL que te proporciona para ver el proyecto.
   _(Generalmente `http://localhost:5173/`)_
