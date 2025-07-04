using System.Text;
using System.Text.Json;

namespace Dropblog.Services;

public class BlogApiService
{
    private readonly HttpClient _httpClient;
    private readonly IBlogConfigurationService _blogConfig;
    private const string ApiKey = "ADMIN_API_KEY";

    public BlogApiService(HttpClient httpClient, IBlogConfigurationService blogConfig)
    {
        _httpClient = httpClient;
        _blogConfig = blogConfig;
        _httpClient.DefaultRequestHeaders.Authorization = 
            new System.Net.Http.Headers.AuthenticationHeaderValue("Bearer", ApiKey);
    }

    private async Task<bool> EnsureBaseUrlAsync()
    {
        if (_blogConfig.CurrentBlogSite == BlogSite.Empty)
        {
            await _blogConfig.LoadBlogSitesAsync();
            if (_blogConfig.CurrentBlogSite == BlogSite.Empty)
                return false;
        }

        var currentUrl = _blogConfig.CurrentBlogSite.Url;
        if (_httpClient.BaseAddress?.ToString() != currentUrl)
        {
            _httpClient.BaseAddress = new Uri(currentUrl);
        }
        return true;
    }

    public async Task<PostResponse> CreatePostAsync(string content, string postType = "note")
    {
        try
        {
            if (!await EnsureBaseUrlAsync())
            {
                return new PostResponse 
                { 
                    Success = false, 
                    Message = "No blog site configured. Please configure a blog site first." 
                };
            }

            var requestData = new { content, post_type = postType };
            var json = JsonSerializer.Serialize(requestData);
            var httpContent = new StringContent(json, Encoding.UTF8, "application/json");

            var response = await _httpClient.PostAsync("/admin/posts", httpContent);
            var responseContent = await response.Content.ReadAsStringAsync();

            if (response.IsSuccessStatusCode)
            {
                var apiResponse = JsonSerializer.Deserialize<PostResponse>(responseContent, new JsonSerializerOptions
                {
                    PropertyNamingPolicy = JsonNamingPolicy.SnakeCaseLower
                });
                return apiResponse ?? new PostResponse { Success = false, Message = "Invalid response" };
            }
            else
            {
                var errorResponse = JsonSerializer.Deserialize<PostResponse>(responseContent, new JsonSerializerOptions
                {
                    PropertyNamingPolicy = JsonNamingPolicy.SnakeCaseLower
                });
                return errorResponse ?? new PostResponse { Success = false, Message = $"Request failed with status {response.StatusCode}" };
            }
        }
        catch (Exception ex)
        {
            return new PostResponse
            {
                Success = false,
                Message = $"Network error: {ex.Message}"
            };
        }
    }

    public async Task<PostTypesResponse> GetPostTypesAsync()
    {
        try
        {
            if (!await EnsureBaseUrlAsync())
            {
                return new PostTypesResponse 
                { 
                    Success = false, 
                    Message = "No blog site configured. Please configure a blog site first." 
                };
            }

            var response = await _httpClient.GetAsync("/admin/post-types");
            var responseContent = await response.Content.ReadAsStringAsync();

            if (response.IsSuccessStatusCode)
            {
                var apiResponse = JsonSerializer.Deserialize<PostTypesApiResponse>(responseContent, new JsonSerializerOptions
                {
                    PropertyNamingPolicy = JsonNamingPolicy.SnakeCaseLower
                });

                if (apiResponse?.Success == true && apiResponse.PostTypes != null)
                {
                    var postTypes = apiResponse.PostTypes.Select(pt => new PostTypeInfo(
                        pt.Slug ?? "",
                        pt.Name ?? "",
                        pt.IconPath ?? $"/post-types/{pt.IconFilename}"
                    )).ToList();

                    return new PostTypesResponse
                    {
                        Success = true,
                        Message = "Post types loaded successfully",
                        PostTypes = postTypes
                    };
                }
                else
                {
                    return new PostTypesResponse
                    {
                        Success = false,
                        Message = apiResponse?.Message ?? "Failed to parse post types response"
                    };
                }
            }
            else
            {
                return new PostTypesResponse
                {
                    Success = false,
                    Message = $"Request failed with status {response.StatusCode}"
                };
            }
        }
        catch (Exception ex)
        {
            return new PostTypesResponse
            {
                Success = false,
                Message = $"Network error: {ex.Message}"
            };
        }
    }

    public async Task<PostTypeResponse> CreatePostTypeAsync(PostTypeCreateRequest request)
    {
        try
        {
            if (!await EnsureBaseUrlAsync())
            {
                return new PostTypeResponse 
                { 
                    Success = false, 
                    Message = "No blog site configured. Please configure a blog site first." 
                };
            }

            var json = JsonSerializer.Serialize(request, new JsonSerializerOptions
            {
                PropertyNamingPolicy = JsonNamingPolicy.SnakeCaseLower
            });
            var httpContent = new StringContent(json, Encoding.UTF8, "application/json");

            var response = await _httpClient.PostAsync("/admin/post-types", httpContent);
            var responseContent = await response.Content.ReadAsStringAsync();

            var apiResponse = JsonSerializer.Deserialize<PostTypeResponse>(responseContent, new JsonSerializerOptions
            {
                PropertyNamingPolicy = JsonNamingPolicy.SnakeCaseLower
            });

            return apiResponse ?? new PostTypeResponse { Success = false, Message = "Invalid response" };
        }
        catch (Exception ex)
        {
            return new PostTypeResponse
            {
                Success = false,
                Message = $"Network error: {ex.Message}"
            };
        }
    }

    public async Task<PostTypeStatsResponse> GetPostTypeStatsAsync()
    {
        try
        {
            if (!await EnsureBaseUrlAsync())
            {
                return new PostTypeStatsResponse 
                { 
                    Success = false, 
                    Message = "No blog site configured. Please configure a blog site first." 
                };
            }

            var response = await _httpClient.GetAsync("/admin/post-types/stats");
            var responseContent = await response.Content.ReadAsStringAsync();

            if (response.IsSuccessStatusCode)
            {
                var apiResponse = JsonSerializer.Deserialize<PostTypeStatsResponse>(responseContent, new JsonSerializerOptions
                {
                    PropertyNamingPolicy = JsonNamingPolicy.SnakeCaseLower
                });
                return apiResponse ?? new PostTypeStatsResponse { Success = false, Message = "Invalid response" };
            }
            else
            {
                return new PostTypeStatsResponse
                {
                    Success = false,
                    Message = $"Request failed with status {response.StatusCode}"
                };
            }
        }
        catch (Exception ex)
        {
            return new PostTypeStatsResponse
            {
                Success = false,
                Message = $"Network error: {ex.Message}"
            };
        }
    }
}

// Response models
public class PostResponse
{
    public bool Success { get; set; }
    public string Message { get; set; } = string.Empty;
    public int PostId { get; set; }
    public string PostHash { get; set; } = string.Empty;
    public string PostUrl { get; set; } = string.Empty;
    public PostTypeInfo? PostType { get; set; }
    public string[]? ValidTypes { get; set; }
}

public class PostTypesResponse
{
    public bool Success { get; set; }
    public string Message { get; set; } = string.Empty;
    public List<PostTypeInfo> PostTypes { get; set; } = new();
}

public class PostTypeResponse
{
    public bool Success { get; set; }
    public string Message { get; set; } = string.Empty;
    public PostTypeInfo? PostType { get; set; }
}

public class PostTypeStatsResponse
{
    public bool Success { get; set; }
    public string Message { get; set; } = string.Empty;
    public List<PostTypeStats> PostTypeStats { get; set; } = new();
    public int TotalTypes { get; set; }
}

// Internal API response models
internal class PostTypesApiResponse
{
    public bool Success { get; set; }
    public string Message { get; set; } = string.Empty;
    public List<PostTypeApiData> PostTypes { get; set; } = new();
    public int TotalCount { get; set; }
}

internal class PostTypeApiData
{
    public int Id { get; set; }
    public string? Slug { get; set; }
    public string? Name { get; set; }
    public string? Description { get; set; }
    public string? IconFilename { get; set; }
    public string? IconPath { get; set; }
    public bool IsActive { get; set; }
    public int SortOrder { get; set; }
}

// Public data models
public record PostTypeInfo(string Slug, string Name, string IconPath);

public record PostTypeCreateRequest(
    string Slug, 
    string Name, 
    string? Description = null, 
    string IconFilename = "",
    int SortOrder = 0
);

public record PostTypeStats(
    int Id, 
    string Slug, 
    string Name, 
    string IconFilename, 
    int PostCount
); 