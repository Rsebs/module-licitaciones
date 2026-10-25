import Swal, { type SweetAlertIcon } from 'sweetalert2'

type icons = SweetAlertIcon
const swalWithBootstrapButtons = Swal.mixin({
  customClass: {
    actions: 'd-flex align-items-center gap-3',
    cancelButton: 'btn btn-danger',
    confirmButton: 'btn btn-success',
  },
  buttonsStyling: false,
})

export const notify = (title: string, text: string, icon: icons) => {
  swalWithBootstrapButtons.fire({
    title,
    text,
    icon,
  })
}

export const warningNotify = async (
  title: string = '¿Estás seguro?',
  text: string = 'Esta acción no se puede revertir',
  confirmButtonText: string = 'Sí, estoy seguro',
  cancelButtonText: string = 'Cancelar',
) => {
  const response = await swalWithBootstrapButtons.fire({
    title,
    text,
    icon: 'warning',
    showCancelButton: true,
    confirmButtonText,
    cancelButtonText,
    reverseButtons: true,
  })

  return response.isConfirmed
}
