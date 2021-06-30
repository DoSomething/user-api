import { RestApiClient } from '@dosomething/gateway';

class RogueClient extends RestApiClient {
  getPosts(options) {
    return this.get('api/v3/posts', options);
  }

  postReview(fields) {
    return this.post(`api/v3/posts/${fields.post_id}/reviews`, fields);
  }
}

export default RogueClient;
