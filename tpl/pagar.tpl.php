<h1>Pagos</h1>
<p>QueBuenaEstoy.com te paga USD $0.50 por cada vez que alguien te agrega como contacto, ese es dinero que podemos enviarte a tu cuenta de PayPal para que tú puedas transferirlo a tu cuenta personal y hacerlo efectivo.</p>
<p>Actualmente tu balance es: USD <?php echo sesion::obtenerBalance(); ?></p>
<form method="post" action="pagar.html">
    Tu correo de PayPal: <input name="correo" value="<?php sesion::info('correo'); ?>" /> <input type="submit" name="pagar" value="Pagarme a esta cuenta de AlertPay" />
</form>
<p>Si no tienes cuenta de PayPal puedes <a href="https://www.paypal.com/" target="_blank">crearla haciendo click aquí</a></p>