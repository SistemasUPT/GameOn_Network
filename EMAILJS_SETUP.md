# 📧 Configuración de EmailJS para GameOn Network

## 🎯 Objetivo
Este documento explica cómo configurar EmailJS para que el sistema de recuperación de contraseñas pueda enviar emails reales a los usuarios.

## 📋 Requisitos Previos
- Cuenta de email (Gmail, Outlook, Yahoo, etc.)
- Acceso a internet
- Navegador web moderno

## 🚀 Pasos de Configuración

### 1. Crear Cuenta en EmailJS

1. Ve a [https://www.emailjs.com/](https://www.emailjs.com/)
2. Haz clic en "Sign Up" para crear una cuenta gratuita
3. Verifica tu email y completa el registro
4. Inicia sesión en tu dashboard

### 2. Configurar Servicio de Email

1. En el dashboard, ve a **"Email Services"**
2. Haz clic en **"Add New Service"**
3. Selecciona tu proveedor de email:
   - **Gmail** (recomendado)
   - **Outlook/Hotmail**
   - **Yahoo**
   - **Otros**

#### Para Gmail:
1. Selecciona "Gmail"
2. Haz clic en "Connect Account"
3. Autoriza EmailJS para acceder a tu cuenta Gmail
4. Asigna un **Service ID** (ej: `service_gameon`)
5. Guarda la configuración

### 3. Crear Template de Email

1. Ve a **"Email Templates"**
2. Haz clic en **"Create New Template"**
3. Configura el template:

**Template ID:** `template_password_recove` (máximo 25 caracteres)

**Subject:** `Recuperación de Contraseña - GameOn Network`

**Content (HTML):**
```html
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 20px; text-align: center; border-radius: 8px 8px 0 0; }
        .content { background: #f9f9f9; padding: 30px; border-radius: 0 0 8px 8px; }
        .button { display: inline-block; background: #667eea; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; margin: 20px 0; }
        .warning { background: #fff3cd; border: 1px solid #ffeaa7; padding: 15px; border-radius: 5px; margin: 20px 0; }
        .footer { text-align: center; color: #666; font-size: 12px; margin-top: 20px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🏃‍♂️ GameOn Network</h1>
            <h2>Recuperación de Contraseña</h2>
        </div>
        
        <div class="content">
            <p>Hola <strong>{{to_name}}</strong>,</p>
            
            <p>Hemos recibido una solicitud para restablecer la contraseña de tu cuenta de <strong>{{user_type}}</strong> en GameOn Network.</p>
            
            <p>Si solicitaste este cambio, haz clic en el siguiente botón para crear una nueva contraseña:</p>
            
            <div style="text-align: center;">
                <a href="{{recovery_link}}" class="button">🔐 Restablecer Contraseña</a>
            </div>
            
            <div class="warning">
                <strong>⚠️ Importante:</strong>
                <ul>
                    <li>Este enlace expirará el <strong>{{expiration_time}}</strong></li>
                    <li>El enlace solo funciona una vez</li>
                    <li>Nunca compartas este enlace con nadie</li>
                </ul>
            </div>
            
            <p>Si no solicitaste este cambio, puedes ignorar este email. Tu contraseña permanecerá sin cambios.</p>
            
            <p>Si tienes problemas con el enlace, cópialo y pégalo en tu navegador:</p>
            <p style="word-break: break-all; background: #f0f0f0; padding: 10px; border-radius: 3px;">{{recovery_link}}</p>
        </div>
        
        <div class="footer">
            <p>Saludos,<br><strong>Equipo GameOn Network</strong></p>
            <p>Este es un email automático, por favor no respondas a este mensaje.</p>
        </div>
    </div>
</body>
</html>
```

4. Configura las **Variables del Template:**
   
   **IMPORTANTE:** En EmailJS, las variables se configuran automáticamente cuando las usas en el template HTML. Solo necesitas:
   
   a) **Usar las variables en el HTML** (ya están incluidas en el código de arriba):
   - `{{to_email}}` - Email del destinatario
   - `{{to_name}}` - Nombre del usuario  
   - `{{recovery_link}}` - Enlace de recuperación
   - `{{expiration_time}}` - Fecha de expiración
   - `{{user_type}}` - Tipo de usuario
   
   b) **EmailJS detecta automáticamente** estas variables del HTML
   
   c) **NO necesitas configurar nada más** - las variables se crean solas
   
   📝 **Nota:** Si quieres ver las variables detectadas, ve a la pestaña "Settings" del template después de guardarlo.

5. Guarda el template

### 4. Obtener Credenciales

1. Ve a **"Account"** → **"General"**
2. Copia tu **Public Key**
3. Ve a **"Email Services"** y copia tu **Service ID**
4. Ve a **"Email Templates"** y copia tu **Template ID**

### 5. Configurar el Sistema

1. Abre el archivo `Config/emailjs_config.js`
2. Reemplaza los valores:

```javascript
const EMAILJS_CONFIG = {
    publicKey: 'tu_public_key_real',      // Reemplazar
    serviceID: 'service_gameon',          // Reemplazar
    templateID: 'template_password_recove' // Reemplazar (máximo 25 caracteres)
};
```

## 🧪 Probar la Configuración

1. Ve a `Views/Auth/forgot-password.php`
2. Ingresa un email válido registrado en el sistema
3. Selecciona el tipo de usuario
4. Haz clic en "Enviar enlace de recuperación"
5. Revisa tu bandeja de entrada (y spam)

## 🔧 Solución de Problemas

### Error: "EmailJS no está configurado"
- Verifica que hayas actualizado las credenciales en `emailjs_config.js`
- Asegúrate de que no queden valores como `TU_PUBLIC_KEY_AQUI`

### Error: "Service not found"
- Verifica que el Service ID sea correcto
- Asegúrate de que el servicio esté activo en EmailJS

### Error: "Template not found"
- Verifica que el Template ID sea correcto
- Asegúrate de que el template esté guardado

### Email no llega
- Revisa la carpeta de spam
- Verifica que el email de destino sea válido
- Comprueba los límites de EmailJS (plan gratuito: 200 emails/mes)

### Error de CORS
- Asegúrate de estar ejecutando desde un servidor (localhost)
- No abras el archivo directamente en el navegador

## 📊 Límites del Plan Gratuito

- **200 emails por mes**
- **2 servicios de email**
- **3 templates**
- **Soporte básico**

Para más emails, considera actualizar a un plan de pago.

## 🔒 Seguridad

- **Nunca** expongas tu Private Key (solo usa Public Key)
- **Nunca** hardcodees credenciales en el código
- Usa variables de entorno en producción
- Monitorea el uso para detectar abusos

## 📞 Soporte

Si tienes problemas:
1. Revisa la consola del navegador para errores
2. Verifica la configuración paso a paso
3. Consulta la documentación de EmailJS: [https://www.emailjs.com/docs/](https://www.emailjs.com/docs/)
4. Contacta al equipo de desarrollo

---

**¡Listo!** Una vez configurado, el sistema podrá enviar emails de recuperación de contraseña automáticamente. 🎉