/* eslint-disable class-methods-use-this */
import { RestApiClient } from '@dosomething/gateway';
import Posts from '../../_mocks_/__mockData__/posts.json';
import Events from '../../_mocks_/__mockData__/events.json';

class RogueClient extends RestApiClient {
  getPosts() {
    return new Promise((resolve, reject) => {
      const error = false;

      if (error) {
        reject(Error('It broke'));
      }

      resolve(Posts);
    });
  }

  postReview(fields) {
    return new Promise((resolve, reject) => {
      const error = false;

      if (error) {
        reject(Error('It broke'));
      }

      resolve({
        data: {
          id: 1,
          signup_id: 1,
          northstar_id: '1234',
          media: {
            url:
              'https://s3.amazonaws.com/ds-rogue-test/uploads/reportback-items/12-1484929292.jpeg',
            caption: 'Est blanditiis ab quo sequi quis.',
          },
          status: fields.status,
          source: 'phoenix-web',
          remote_addr: '0.0.0.0',
          created_at: '2017-04-28T20:14:49+00:00',
          updated_at: '2017-04-28T20:22:23+00:00',
        },
      });
    });
  }
}

export default RogueClient;
