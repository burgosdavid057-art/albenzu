# Albenzu — cuestionario para levantar el requerimiento

Guía para la reunión con el cliente. La demo ya responde por defecto casi todo; el objetivo
de estas preguntas es **confirmar, ajustar o descartar**, no empezar de cero. Muéstrale la
maqueta funcionando y ve preguntando sobre lo que está viendo: rinde mucho más que una
lista en frío.

Marca cada respuesta como **✅ como está** / **✏️ cambiar** / **❌ quitar** / **➕ agregar**.

> Este documento es interno. No se sube al servidor del cliente.

---

## 0. Antes de la reunión — lleva esto listo

- La demo corriendo (local o en `davidburgos.dev/albenzu`).
- El panel abierto en Pedidos, para mostrar cómo entra un pedido en vivo.
- Un pedido de prueba hecho desde el celular, para que lo vea en el teléfono.

---

## 1. El negocio y el alcance

1. ¿Cuántas sedes tiene Albenzu hoy? ¿La web es solo para la de la 80 o hay más?
   *(Cambia todo: si son varias, hay que elegir sede en el pedido y separar la caja de cada una.)*
2. ¿Quién va a manejar el panel día a día? ¿El dueño, la administradora, el cajero?
   *(Define cuántos usuarios y si hace falta separar permisos.)*
3. ¿Cuántos pedidos a domicilio manejan hoy al día, más o menos? ¿En qué franjas se concentran?
4. ¿Hoy por dónde reciben los domicilios? (WhatsApp, teléfono, Rappi, DiDi, iFood…)
5. ¿La web reemplaza esos canales o convive con ellos?
   *(Si conviven, vale la pena poder cargar a mano en el panel un pedido que entró por WhatsApp.)*
6. ¿Qué es lo que más les duele hoy del proceso actual? *(Ahí está el verdadero requerimiento.)*
7. ¿Para cuándo la necesitan? ¿Hay alguna fecha o campaña detrás?

---

## 2. La carta

8. ¿La carta que cargué está completa y con los precios al día? *(La saqué de su canal de domicilios.)*
9. ¿Hay platos que **no** quieren vender por la web? (porque no viajan bien, por ejemplo)
10. ¿Los precios de domicilio son los mismos que en el restaurante o llevan recargo?
11. ¿Hay **menú del día** o almuerzo ejecutivo que cambie a diario? ¿Quién lo cargaría y a qué hora?
12. ¿Hay platos que solo se venden ciertos días? (la Sopa de la abuela es de fin de semana)
    *(Hoy se maneja con el switch «disponible hoy»; si es fijo por día, hay que programarlo.)*
13. ¿Manejan **tamaños** por plato (personal / media / grande) o son platos distintos?
14. ¿Y **opciones** dentro de un plato (término de la carne, tipo de jugo, con o sin cebolla)?
    *(Hoy hay adiciones + nota libre. Si necesitan opciones obligatorias, es un desarrollo aparte.)*
15. ¿Las adiciones que cargué son las correctas? ¿Aplican a todos los platos o solo a algunos?
16. ¿Tienen combos o promos armadas? ¿2x1, combo familiar, descuento por día?
17. ¿Tienen fotos de los platos? ¿En qué formato y quién las puede pasar?
    *(Si no hay, se puede hacer una sesión de fotos; mientras tanto van las ilustraciones.)*
18. ¿Manejan bebidas alcohólicas? *(Implica aviso legal y verificación de edad.)*

---

## 3. Domicilio y recogida

19. ¿Cuál es la zona real de cobertura? ¿Barrios o radio en kilómetros?
20. ¿El domicilio es un valor único o cambia por zona?
    *(Hoy es único. Si cambia por zona, hay que hacer tabla de barrios con tarifa.)*
21. ¿Manejan **domicilio gratis** desde algún monto? ¿Cuál?
22. ¿Hay **pedido mínimo**? ¿Cuánto?
23. ¿Los domiciliarios son propios o tercerizados?
    *(Si son propios, puede valer la pena asignar el pedido a un domiciliario en el panel.)*
24. ¿Cuánto se demoran realmente? ¿Y en hora pico?
25. ¿Quieren permitir **pedidos programados** ("para las 7 de la noche")?
26. Para recoger: ¿el cliente llega a caja o hay un punto de entrega aparte?
27. ¿Quieren aceptar **reservas de mesa** por la web? *(Es otro módulo, hay que cotizarlo aparte.)*

---

## 4. Pagos — el punto más importante

28. ¿Con qué **pasarela** quieren trabajar? (Wompi, Bold, Mercado Pago, ePayco)
    *(Yo recomiendo ___ por comisión y facilidad de integración.)*
29. ¿Tienen ya cuenta de comercio abierta con alguna? ¿A nombre de quién queda?
    *(Sin esto no se puede activar la tarjeta: la cuenta la abre el restaurante, no yo.)*
30. ¿Quieren aceptar **PSE / transferencia** además de tarjeta?
31. ¿Nequi o Daviplata? ¿A qué número o QR?
32. ¿El efectivo se cobra al recibir o quieren pedir confirmación previa?
33. ¿Necesitan que el sistema calcule la **devuelta**? *(Ya lo hace: el cliente dice con cuánto paga.)*
34. ¿Quién asume la comisión de la pasarela? ¿Se refleja en el precio?
35. ¿Necesitan **factura electrónica**? ¿Con qué proveedor facturan hoy (Siigo, Alegra, World Office)?
    *(Si hay que integrar facturación, es un desarrollo aparte y hay que cotizarlo.)*
36. Datos legales para la web: razón social, NIT, régimen, si son responsables de IVA.
37. ¿Los precios de la carta ya incluyen impuestos? ¿Cobran **propina sugerida**?

---

## 5. Operación del pedido (cómo lo vive la cocina)

38. Cuando entra un pedido, ¿qué pasa hoy paso a paso? *(Que te lo cuenten completo.)*
39. ¿Cómo quieren **enterarse** de que entró un pedido?
    - Sonido/alerta en la pantalla del panel
    - WhatsApp al celular del restaurante
    - Correo
    - Impresión automática de la comanda
40. ¿Tienen impresora térmica en cocina? ¿De qué marca? *(Define si se puede imprimir automático.)*
41. ¿Quién cambia el estado del pedido? ¿La cocina, la caja o el domiciliario?
42. ¿Los estados que puse les sirven? (recibido → confirmado → en preparación → en camino → entregado)
43. ¿Qué pasa si un plato se agota a mitad de servicio? ¿Llaman al cliente, lo cambian, lo cancelan?
44. ¿Quieren poder **cerrar el canal en línea** en un clic cuando estén copados? *(Ya está en Ajustes.)*
45. ¿Necesitan un **cierre de caja** del día por método de pago? *(Hoy hay exportación a CSV.)*

---

## 6. Cliente y comunicación

46. ¿Quieren que el cliente **cree cuenta** o que pida sin registrarse?
    *(Hoy pide sin registro y el sistema guarda su ficha por el celular. Es lo que más convierte.)*
47. ¿Quieren mandar **confirmación automática por WhatsApp**?
    *(Requiere WhatsApp Business API o un servicio tipo n8n + proveedor; tiene costo mensual.)*
48. ¿Correo de confirmación? ¿Desde qué dirección?
49. **Celular comercial de WhatsApp** para los botones de la web (el enlace del perfil no permite mensaje precargado).
50. ¿Quieren **programa de fidelización**? (a los 10 pedidos, uno gratis; cumpleaños; etc.)
51. ¿Quieren pedirle **calificación** al cliente después de entregar?
52. ¿Van a usar los cupones? ¿Para qué campañas? ¿Los maneja alguien de mercadeo?
53. ¿Qué hacen con la base de clientes? ¿Piensan mandar promociones?
    *(Ojo: hay que poner la autorización de tratamiento de datos — Ley 1581 de Colombia.)*

---

## 7. Marca y contenido

54. ¿Tienen el **logo en alta** (PNG con fondo transparente, AI, EPS o SVG)?
55. ¿Tienen manual de marca o colores definidos? *(Yo saqué el rojo y el crema del logo del perfil.)*
56. ¿La historia que escribí es correcta? *(Alicia y Benjamín Zuluaga, 1982, de ahí AL-BEN-ZU.)*
57. ¿Hay algo que quieran destacar sí o sí en la portada? ¿Un plato insignia, un premio, una nota de prensa?
58. ¿Fotos del local, del equipo, de la cocina?
59. ¿Quieren mostrar reseñas reales de Google o TripAdvisor?
60. ¿Redes que quieren enlazar? ¿Alguna que no usen ya y haya que quitar?

---

## 8. Dominio, hosting y entrega

61. ¿Tienen **dominio** propio? ¿Cuál y con quién está registrado?
    *(Si no, hay que comprarlo. Recomiendo `albenzu.co`.)*
62. ¿Tienen hosting o correo corporativo hoy? ¿Con quién?
63. ¿A nombre de quién quedan el dominio y el hosting? *(Que quede claro desde ya.)*
64. ¿Quieren que la web reemplace lo que tienen o que conviva?
65. ¿Necesitan **capacitación** para el panel? ¿Presencial o por videollamada? ¿Cuántas personas?
66. ¿Quién queda como contacto técnico para dudas después de entregar?
67. ¿Van a querer **soporte y mantenimiento mensual**? *(Actualizaciones, respaldos, cambios de carta.)*

---

## 9. Cierre comercial

68. ¿Hay presupuesto definido para el proyecto?
69. ¿Prefieren pago único o pago inicial + mensualidad?
    *(La mensualidad tiene sentido si hay pasarela, soporte y cambios de carta frecuentes.)*
70. ¿Qué **no** puede faltar para que consideren el proyecto exitoso? *(Escribe la respuesta textual.)*
71. ¿Hay alguien más que tenga que aprobar? ¿Socio, contador, la familia?

---

## Checklist de lo que necesito recibir para arrancar

- [ ] Logo en alta (SVG / PNG transparente / AI)
- [ ] Fotos de los platos (o luz verde para la sesión de fotos)
- [ ] Carta definitiva con precios confirmados
- [ ] Celular de WhatsApp comercial
- [ ] Correo para las confirmaciones
- [ ] Razón social, NIT y datos de facturación
- [ ] Zona de cobertura y tarifas de domicilio
- [ ] Cuenta de comercio de la pasarela (llaves de producción)
- [ ] Dominio y accesos al hosting (o autorización para comprarlos)
- [ ] Nombre y contacto de quién va a manejar el panel

---

## Resumen para llenar al final de la reunión

| Punto | Definido |
|---|---|
| Alcance (sedes, módulos incluidos) | |
| Pasarela de pago elegida | |
| Zona y tarifas de domicilio | |
| Notificaciones de pedido (cómo se enteran) | |
| Dominio y hosting | |
| Fecha de entrega comprometida | |
| Precio y forma de pago | |
| Soporte mensual (sí/no y valor) | |
