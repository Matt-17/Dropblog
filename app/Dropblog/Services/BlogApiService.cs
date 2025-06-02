using System.Text;
using System.Text.Json;

namespace Dropblog.Services;

public class BlogApiService
{
    private readonly HttpClient _httpClient;
    private const string BaseUrl = "https://numbertools.de";
    private const string ApiKey = "ADMIN_API_KEY";

    public BlogApiService(HttpClient httpClient)
    {
        _httpClient = httpClient;
        _httpClient.BaseAddress = new Uri(BaseUrl);
        _httpClient.DefaultRequestHeaders.Authorization = 
            new System.Net.Http.Headers.AuthenticationHeaderValue("Bearer", ApiKey);
    }

    public async Task<PostResponse> CreatePostAsync(string content, string type = "note")
    {
        try
        {
            var requestData = new { content, type };
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
}

public class PostResponse
{
    public bool Success { get; set; }
    public string Message { get; set; } = string.Empty;
    public int PostId { get; set; }
    public string PostHash { get; set; } = string.Empty;
    public string PostUrl { get; set; } = string.Empty;
    public string Type { get; set; } = string.Empty;
    public string Icon { get; set; } = string.Empty;
    public string[]? ValidTypes { get; set; }
} 