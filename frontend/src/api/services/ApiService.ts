import { ApiError, type ApiErroResponse, type ApiResponse } from '@/api/types/axiosResponse.type'
import axios, { type AxiosInstance } from 'axios'

export default class AxiosService {
  static axiosInstance: AxiosInstance = axios.create({
    baseURL: import.meta.env.VITE_API_BASE_URL,
    headers: {
      'Content-Type': 'application/json',
    },
  })

  private static handleError(error: unknown): never {
    if (axios.isAxiosError(error) && error.response?.data) {
      const err = error.response.data as ApiErroResponse
      throw new ApiError(err)
    }

    const errorResponse = {
      status: 'error',
      message: 'Something went wrong',
      code: 0,
      errors: error instanceof Error ? error.message : 'Unknown error',
    } satisfies ApiErroResponse

    throw new ApiError(errorResponse)
  }

  static MIME_TYPES: Record<string, string> = {
    'application/pdf': 'pdf',
    'image/jpeg': 'jpg',
    'image/png': 'png',
    'application/zip': 'zip',
    'text/csv': 'csv',
    'application/vnd.ms-excel': 'xls',
    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet': 'xlsx',
    'application/vnd.openxmlformats-officedocument.wordprocessingml.document': 'docx',
  }

  static async get<T = unknown>(
    url: string,
    params?: Record<string, unknown>,
  ): Promise<ApiResponse<T>> {
    try {
      const response = await this.axiosInstance.get(url, { params })
      return response.data
    } catch (error) {
      this.handleError(error)
    }
  }

  static async post<T = unknown>(
    url: string,
    data?: Record<string, unknown>,
    params?: Record<string, unknown>,
  ): Promise<ApiResponse<T>> {
    try {
      const response = await this.axiosInstance.post(url, data, { params })
      return response.data
    } catch (error) {
      this.handleError(error)
    }
  }

  static async postWithFile<T = unknown>(
    url: string,
    data?: Record<string, unknown>,
    params?: Record<string, unknown>,
  ): Promise<ApiResponse<T>> {
    try {
      const response = await this.axiosInstance.post(url, data, {
        params,
        headers: { 'Content-Type': 'multipart/form-data' },
      })
      return response.data
    } catch (error) {
      this.handleError(error)
    }
  }

  static async put<T = unknown>(
    url: string,
    data?: Record<string, unknown>,
    params?: Record<string, unknown>,
  ): Promise<ApiResponse<T>> {
    try {
      const response = await this.axiosInstance.put(url, data, { params })
      return response.data
    } catch (error) {
      this.handleError(error)
    }
  }

  static async delete<T = unknown>(
    url: string,
    params?: Record<string, unknown>,
  ): Promise<ApiResponse<T>> {
    try {
      const response = await this.axiosInstance.delete(url, { params })
      return response.data
    } catch (error) {
      this.handleError(error)
    }
  }

  static async download(
    url: string,
    fileName: string = 'archivo',
    params?: Record<string, unknown>,
  ): Promise<void> {
    try {
      const response = await this.axiosInstance.get<Blob>(url, {
        params,
        responseType: 'blob',
      })

      const headers = response.headers as Record<string, unknown>
      const contentType = String(headers['content-type'] || '')
      const extension = this.MIME_TYPES[contentType] || 'bin'

      const cleanFileName = fileName.replace(/\.[^/.]+$/, '')

      const downloadUrl = window.URL.createObjectURL(new Blob([response.data]))
      const link = document.createElement('a')
      link.href = downloadUrl

      link.setAttribute('download', `${cleanFileName}.${extension}`)

      document.body.appendChild(link)
      link.click()

      document.body.removeChild(link)
      window.URL.revokeObjectURL(downloadUrl)
    } catch (error) {
      this.handleError?.(error)
    }
  }
}
