/**
 * WordPress REST API Client
 * 
 * Handles all communication with WordPress via REST API
 * using Application Password authentication
 */

import axios, { AxiosInstance, AxiosError } from 'axios';
import { logger, logWordPressCall, logError } from '../utils/logger.js';

export interface CreateContentRequest {
  post_type: 'post' | 'page';
  title: string;
  blocks: any[];
  status?: 'draft' | 'pending';
  excerpt?: string;
  categories?: number[];
  tags?: number[];
}

export interface UpdateContentRequest {
  blocks: any[];
  title?: string;
  create_revision?: boolean;
}

export class WordPressClient {
  private baseUrl: string;
  private auth: string;
  private axios: AxiosInstance;
  private connected: boolean = false;

  constructor() {
    this.baseUrl = process.env.WORDPRESS_URL!;
    
    // Base64 encode credentials for Basic Auth
    const credentials = `${process.env.WP_USERNAME}:${process.env.WP_APP_PASSWORD}`;
    this.auth = Buffer.from(credentials).toString('base64');

    // Create axios instance
    this.axios = axios.create({
      baseURL: `${this.baseUrl}/wp-json/nexter-mcp/v1`,
      headers: {
        'Authorization': `Basic ${this.auth}`,
        'Content-Type': 'application/json',
        'User-Agent': 'Nexter-MCP-Server/1.0'
      },
      timeout: parseInt(process.env.WP_API_TIMEOUT || '30000', 10)
    });

    // Request interceptor for logging
    this.axios.interceptors.request.use(
      (config) => {
        config.meta = { startTime: Date.now() };
        return config;
      },
      (error) => {
        return Promise.reject(error);
      }
    );

    // Response interceptor for logging and retry
    this.axios.interceptors.response.use(
      (response) => {
        const duration = Date.now() - (response.config.meta?.startTime || 0);
        logWordPressCall(
          response.config.url || '',
          response.config.method?.toUpperCase() || '',
          response.status,
          duration
        );
        return response;
      },
      async (error: AxiosError) => {
        const config: any = error.config;
        const duration = Date.now() - (config?.meta?.startTime || 0);
        
        logWordPressCall(
          config?.url || '',
          config?.method?.toUpperCase() || '',
          error.response?.status || 0,
          duration
        );

        // Retry logic for transient errors
        if (!config || !config.retry) {
          config.retry = 0;
        }

        const maxRetries = parseInt(process.env.WP_API_RETRY_ATTEMPTS || '3', 10);
        const shouldRetry = config.retry < maxRetries && 
                           error.response?.status &&
                           error.response.status >= 500;

        if (shouldRetry) {
          config.retry += 1;
          const delay = 1000 * Math.pow(2, config.retry); // Exponential backoff
          logger.warn(`Retrying WordPress API call (attempt ${config.retry}/${maxRetries}) after ${delay}ms`);
          
          await new Promise(resolve => setTimeout(resolve, delay));
          return this.axios(config);
        }

        logError('WordPress API Error', error, {
          url: config?.url,
          method: config?.method,
          status: error.response?.status,
          data: error.response?.data
        });

        return Promise.reject(error);
      }
    );

    // Test connection on initialization
    this.testConnection().catch(() => {
      logger.warn('Initial WordPress connection test failed - will retry on first request');
    });
  }

  /**
   * Test WordPress connection
   */
  async testConnection(): Promise<boolean> {
    try {
      // Test with WordPress core API first
      const response = await axios.get(`${this.baseUrl}/wp-json/wp/v2/users/me`, {
        headers: { 'Authorization': `Basic ${this.auth}` },
        timeout: 5000
      });

      if (response.status === 200) {
        this.connected = true;
        logger.info('✅ WordPress connection successful', {
          site: this.baseUrl,
          user: response.data.name
        });
        return true;
      }

      return false;
    } catch (error: any) {
      this.connected = false;
      logger.error('WordPress connection failed', {
        site: this.baseUrl,
        error: error.message
      });
      throw error;
    }
  }

  /**
   * Check if connected to WordPress
   */
  isConnected(): boolean {
    return this.connected;
  }

  /**
   * Get post/page content with parsed blocks
   */
  async getContent(postId: number) {
    try {
      const response = await this.axios.get(`/content/${postId}`);
      return response.data;
    } catch (error: any) {
      throw new Error(`Failed to get content: ${error.response?.data?.message || error.message}`);
    }
  }

  /**
   * Create new post/page
   */
  async createContent(data: CreateContentRequest) {
    try {
      const response = await this.axios.post('/content/create', data);
      return response.data;
    } catch (error: any) {
      throw new Error(`Failed to create content: ${error.response?.data?.message || error.message}`);
    }
  }

  /**
   * Update existing post/page
   */
  async updateContent(postId: number, data: UpdateContentRequest) {
    try {
      const response = await this.axios.post(`/content/${postId}/update`, data);
      return response.data;
    } catch (error: any) {
      throw new Error(`Failed to update content: ${error.response?.data?.message || error.message}`);
    }
  }

  /**
   * Search for posts/pages
   */
  async searchContent(query: string, postType: string = 'post', limit: number = 10) {
    try {
      const response = await this.axios.get('/content/search', {
        params: { query, post_type: postType, limit }
      });
      return response.data;
    } catch (error: any) {
      throw new Error(`Failed to search content: ${error.response?.data?.message || error.message}`);
    }
  }

  /**
   * Analyze blocks in content
   */
  async analyzeBlocks(postId: number) {
    try {
      const response = await this.axios.get(`/content/${postId}/blocks`);
      return response.data;
    } catch (error: any) {
      throw new Error(`Failed to analyze blocks: ${error.response?.data?.message || error.message}`);
    }
  }

  /**
   * Validate blocks before save
   */
  async validateBlocks(blocks: any[], strict: boolean = false) {
    try {
      const response = await this.axios.post('/validate', { blocks, strict });
      return response.data;
    } catch (error: any) {
      throw new Error(`Failed to validate blocks: ${error.response?.data?.message || error.message}`);
    }
  }

  /**
   * Get post revisions
   */
  async getRevisions(postId: number) {
    try {
      const response = await this.axios.get(`/content/${postId}/revisions`);
      return response.data;
    } catch (error: any) {
      throw new Error(`Failed to get revisions: ${error.response?.data?.message || error.message}`);
    }
  }
}

// Extend AxiosRequestConfig to include meta for timing
declare module 'axios' {
  export interface AxiosRequestConfig {
    meta?: {
      startTime: number;
    };
    retry?: number;
  }
}