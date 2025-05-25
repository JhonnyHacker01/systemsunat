/**
 * JavaScript para el módulo de ventas
 */

document.addEventListener('DOMContentLoaded', function() {
    // Variables globales
    const productos = [];
    const igvPorcentaje = 18; // 18% de IGV
    
    // Elementos del DOM
    const formVenta = document.getElementById('formVenta');
    const productoSelect = document.getElementById('producto_select');
    const cantidadInput = document.getElementById('cantidad_producto');
    const precioInput = document.getElementById('precio_producto');
    const btnAgregarProducto = document.getElementById('btnAgregarProducto');
    const tablaProductos = document.getElementById('tablaProductos');
    const filaVacia = document.getElementById('filaVacia');
    const productosJson = document.getElementById('productos_json');
    const tipoComprobanteSelect = document.getElementById('tipo_comprobante');
    const serieSelect = document.getElementById('serie');
    const numeroInput = document.getElementById('numero');
    const clienteSelect = document.getElementById('cliente');
    
    // Selectores para los totales
    const subtotalElement = document.getElementById('subtotal');
    const subtotalValor = document.getElementById('subtotal_valor');
    const igvElement = document.getElementById('igv');
    const igvValor = document.getElementById('igv_valor');
    const totalElement = document.getElementById('total');
    const totalValor = document.getElementById('total_valor');
    
    // Evento al seleccionar un producto
    if (productoSelect) {
        productoSelect.addEventListener('change', function() {
            if (this.value) {
                const option = this.options[this.selectedIndex];
                precioInput.value = option.dataset.precio;
            } else {
                precioInput.value = '';
            }
        });
    }
    
    // Evento al cambiar el tipo de comprobante
    if (tipoComprobanteSelect) {
        tipoComprobanteSelect.addEventListener('change', function() {
            // Filtrar series según el tipo de comprobante seleccionado
            const tipoComprobante = this.value;
            
            if (!tipoComprobante) {
                serieSelect.innerHTML = '<option value="">Seleccione</option>';
                numeroInput.value = '';
                return;
            }
            
            // Mostrar solo las series correspondientes al tipo de comprobante
            const opciones = Array.from(serieSelect.options);
            serieSelect.innerHTML = '<option value="">Seleccione</option>';
            
            opciones.forEach(opcion => {
                if (opcion.value && opcion.dataset.tipo === tipoComprobante) {
                    serieSelect.appendChild(opcion.cloneNode(true));
                }
            });
            
            // Validar cliente según tipo de comprobante
            validarClienteComprobante();
        });
    }
    
    // Evento al cambiar la serie
    if (serieSelect) {
        serieSelect.addEventListener('change', function() {
            if (this.value && tipoComprobanteSelect.value) {
                // Llamada AJAX para obtener el siguiente número
                obtenerSiguienteNumero(tipoComprobanteSelect.value, this.value);
            } else {
                numeroInput.value = '';
            }
        });
    }
    
    // Evento al cambiar el cliente
    if (clienteSelect) {
        clienteSelect.addEventListener('change', validarClienteComprobante);
    }
    
    // Función para validar el cliente según el tipo de comprobante
    function validarClienteComprobante() {
        if (!clienteSelect || !tipoComprobanteSelect) return;
        
        const tipoComprobante = tipoComprobanteSelect.value;
        const clienteOption = clienteSelect.options[clienteSelect.selectedIndex];
        
        if (tipoComprobante === 'Factura' && clienteOption && clienteOption.dataset.documento !== 'RUC') {
            alert('Para facturas, el cliente debe tener RUC.');
            clienteSelect.value = '';
        }
    }
    
    // Función para obtener el siguiente número de comprobante
    function obtenerSiguienteNumero(tipoComprobante, serie) {
        // Simulamos una respuesta para este ejemplo
        // En un entorno real, esto sería una llamada AJAX al servidor
        setTimeout(() => {
            switch (serie) {
                case 'F001':
                    numeroInput.value = '00000123';
                    break;
                case 'B001':
                    numeroInput.value = '00000456';
                    break;
                default:
                    numeroInput.value = '00000001';
            }
        }, 300);
    }
    
    // Evento al hacer clic en el botón de agregar producto
    if (btnAgregarProducto) {
        btnAgregarProducto.addEventListener('click', function() {
            agregarProducto();
        });
    }
    
    // Función para agregar un producto a la tabla
    function agregarProducto() {
        if (!productoSelect.value || !cantidadInput.value || !precioInput.value) {
            alert('Por favor, seleccione un producto, cantidad y precio.');
            return;
        }
        
        const option = productoSelect.options[productoSelect.selectedIndex];
        const idProducto = productoSelect.value;
        const codigo = option.dataset.codigo;
        const nombre = option.dataset.nombre;
        const cantidad = parseInt(cantidadInput.value);
        const precio = parseFloat(precioInput.value);
        const stock = parseInt(option.dataset.stock);
        const afectoIgv = option.dataset.afectoIgv === '1';
        
        // Validar stock disponible
        if (cantidad > stock) {
            alert(`No hay suficiente stock disponible. Stock actual: ${stock}`);
            return;
        }
        
        // Validar si el producto ya está en la tabla
        const productoExistente = productos.findIndex(p => p.id_producto === idProducto);
        if (productoExistente !== -1) {
            // Actualizar cantidad y totales
            productos[productoExistente].cantidad += cantidad;
            recalcularTotales(productoExistente);
            actualizarTablaProductos();
        } else {
            // Calcular totales del producto
            const subtotalProducto = cantidad * precio;
            const igvProducto = afectoIgv ? subtotalProducto * (igvPorcentaje / 100) : 0;
            const totalProducto = subtotalProducto + igvProducto;
            
            // Agregar producto al array
            productos.push({
                id_producto: idProducto,
                codigo: codigo,
                nombre: nombre,
                cantidad: cantidad,
                precio_unitario: precio,
                afecto_igv: afectoIgv,
                subtotal: subtotalProducto,
                igv: igvProducto,
                total: totalProducto
            });
            
            actualizarTablaProductos();
        }
        
        // Limpiar campos
        productoSelect.value = '';
        cantidadInput.value = '1';
        precioInput.value = '';
    }
    
    // Función para recalcular totales de un producto
    function recalcularTotales(index) {
        const producto = productos[index];
        producto.subtotal = producto.cantidad * producto.precio_unitario;
        producto.igv = producto.afecto_igv ? producto.subtotal * (igvPorcentaje / 100) : 0;
        producto.total = producto.subtotal + producto.igv;
    }
    
    // Función para actualizar la tabla de productos
    function actualizarTablaProductos() {
        // Eliminar filas existentes excepto la fila vacía
        const tbody = tablaProductos.querySelector('tbody');
        tbody.innerHTML = '';
        
        if (productos.length === 0) {
            tbody.appendChild(filaVacia);
            actualizarTotalesVenta();
            return;
        }
        
        // Agregar productos a la tabla
        productos.forEach((producto, index) => {
            const fila = document.createElement('tr');
            fila.innerHTML = `
                <td>${producto.codigo}</td>
                <td>${producto.nombre}</td>
                <td>${producto.cantidad}</td>
                <td class="text-end">S/. ${producto.precio_unitario.toFixed(2)}</td>
                <td class="text-end">S/. ${producto.subtotal.toFixed(2)}</td>
                <td>
                    <button type="button" class="btn btn-sm btn-danger btn-eliminar" data-index="${index}">
                        <i class="bi bi-trash"></i>
                    </button>
                </td>
            `;
            tbody.appendChild(fila);
        });
        
        // Asignar evento a los botones de eliminar
        const botonesEliminar = document.querySelectorAll('.btn-eliminar');
        botonesEliminar.forEach(boton => {
            boton.addEventListener('click', function() {
                const index = parseInt(this.dataset.index);
                eliminarProducto(index);
            });
        });
        
        // Actualizar JSON para enviar al servidor
        productosJson.value = JSON.stringify(productos);
        
        // Actualizar totales
        actualizarTotalesVenta();
    }
    
    // Función para eliminar un producto
    function eliminarProducto(index) {
        productos.splice(index, 1);
        actualizarTablaProductos();
    }
    
    // Función para actualizar los totales de la venta
    function actualizarTotalesVenta() {
        let subtotal = 0;
        let igv = 0;
        let total = 0;
        
        productos.forEach(producto => {
            subtotal += producto.subtotal;
            igv += producto.igv;
            total += producto.total;
        });
        
        // Actualizar elementos en la UI
        subtotalElement.textContent = `S/. ${subtotal.toFixed(2)}`;
        subtotalValor.value = subtotal.toFixed(2);
        
        igvElement.textContent = `S/. ${igv.toFixed(2)}`;
        igvValor.value = igv.toFixed(2);
        
        totalElement.textContent = `S/. ${total.toFixed(2)}`;
        totalValor.value = total.toFixed(2);
    }
    
    // Evento de envío del formulario
    if (formVenta) {
        formVenta.addEventListener('submit', function(e) {
            e.preventDefault();
            
            if (productos.length === 0) {
                alert('Debe agregar al menos un producto a la venta.');
                return;
            }
            
            if (!tipoComprobanteSelect.value || !serieSelect.value || !numeroInput.value || !clienteSelect.value) {
                alert('Por favor, complete todos los campos requeridos.');
                return;
            }
            
            // Aquí se enviaría el formulario
            this.submit();
        });
    }
    
    // Gestión del formulario de nuevo cliente
    const formNuevoCliente = document.getElementById('formNuevoCliente');
    const btnGuardarCliente = document.getElementById('btnGuardarCliente');
    
    if (btnGuardarCliente) {
        btnGuardarCliente.addEventListener('click', function() {
            // Validar formulario
            if (!validarFormularioCliente()) {
                return;
            }
            
            // Simular guardado
            alert('Cliente guardado correctamente.');
            
            // Cerrar modal
            const modalNuevoCliente = bootstrap.Modal.getInstance(document.getElementById('modalNuevoCliente'));
            modalNuevoCliente.hide();
        });
    }
    
    // Función para validar formulario de cliente
    function validarFormularioCliente() {
        const tipoDocumento = document.getElementById('cliente_tipo_documento').value;
        const numeroDocumento = document.getElementById('cliente_documento').value;
        const razonSocial = document.getElementById('cliente_razon_social').value;
        
        if (!tipoDocumento || !numeroDocumento || !razonSocial) {
            alert('Por favor, complete los campos requeridos.');
            return false;
        }
        
        // Validar formato de documento según tipo
        if (tipoDocumento === 'DNI' && !/^\d{8}$/.test(numeroDocumento)) {
            alert('El DNI debe tener 8 dígitos numéricos.');
            return false;
        }
        
        if (tipoDocumento === 'RUC' && !/^10\d{8}$|^20\d{8}$/.test(numeroDocumento)) {
            alert('El RUC debe tener 11 dígitos y comenzar con 10 o 20.');
            return false;
        }
        
        return true;
    }
    
    // Botón para buscar documento
    const btnBuscarDocumento = document.getElementById('btnBuscarDocumento');
    if (btnBuscarDocumento) {
        btnBuscarDocumento.addEventListener('click', function() {
            const tipoDocumento = document.getElementById('cliente_tipo_documento').value;
            const numeroDocumento = document.getElementById('cliente_documento').value;
            
            if (!numeroDocumento) {
                alert('Ingrese un número de documento para buscar.');
                return;
            }
            
            // Simular búsqueda
            if (tipoDocumento === 'DNI') {
                setTimeout(() => {
                    document.getElementById('cliente_razon_social').value = 'JUAN PEREZ RODRIGUEZ';
                    document.getElementById('cliente_direccion').value = 'AV. AREQUIPA 123, LIMA';
                }, 500);
            } else if (tipoDocumento === 'RUC') {
                setTimeout(() => {
                    document.getElementById('cliente_razon_social').value = 'EMPRESA EJEMPLO S.A.C.';
                    document.getElementById('cliente_direccion').value = 'JR. HUALLAGA 456, LIMA';
                }, 500);
            }
        });
    }
});