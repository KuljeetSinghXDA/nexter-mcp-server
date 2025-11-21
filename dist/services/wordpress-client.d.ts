/**
 * WordPress REST API Client
 *
 * Handles all communication with WordPress via REST API
 * using Application Password authentication
 */
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
export declare class WordPressClient {
    private baseUrl;
    private auth;
    private axios;
    private connected;
    constructor();
    /**
     * Test WordPress connection
     */
    testConnection(): Promise<boolean>;
    /**
     * Check if connected to WordPress
     */
    isConnected(): boolean;
    /**
     * Get post/page content with parsed blocks
     */
    getContent(postId: number): Promise<any>;
    /**
     * Create new post/page
     */
    createContent(data: CreateContentRequest): Promise<any>;
    /**
     * Update existing post/page
     */
    updateContent(postId: number, data: UpdateContentRequest): Promise<any>;
    /**
     * Search for posts/pages
     */
    searchContent(query: string, postType?: string, limit?: number): Promise<any>;
    /**
     * Analyze blocks in content
     */
    analyzeBlocks(postId: number): Promise<any>;
    /**
     * Validate blocks before save
     */
    validateBlocks(blocks: any[], strict?: boolean): Promise<any>;
    /**
     * Get post revisions
     */
    getRevisions(postId: number): Promise<any>;
}
declare module 'axios' {
    interface AxiosRequestConfig {
        meta?: {
            startTime: number;
        };
        retry?: number;
    }
}
//# sourceMappingURL=wordpress-client.d.ts.map