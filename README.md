| **URL**                                           | **HTTP Method** | **Auth** | **JSON Response**                  |
| ------------------------------------------------- | --------------- | -------- | ---------------------------------- |
| `/user/login`                                     | POST            | ❌        | User's token                       |
| `/users`                                          | GET             | ✅        | All users                          |
| `/artists`                                        | GET             | ❌        | All artists                        |
| `/artist`                                         | POST            | ✅        | New artist added                   |
| `/artist/{id}`                                    | PATCH           | ✅        | Edited artist                      |
| `/artist/{id}`                                    | DELETE          | ✅        | Deleted artist ID                  |
| `/artist/{id}/members`                            | GET             | ❌        | All members of an artist           |
| `/artist/{id}/member`                             | POST            | ✅        | New member added to artist         |
| `/artist/{artist_id}/member/{id}`                 | PATCH           | ✅        | Edited member of the artist        |
| `/artist/{artist_id}/members/{id}`                | DELETE          | ✅        | Deleted member ID                  |
| `/artist/{id}/albums`                             | GET             | ❌        | All albums of an artist            |
| `/artist/{id}/album`                              | POST            | ✅        | New album added to artist          |
| `/artist/{artist_id}/album/{id}`                  | PATCH           | ✅        | Edited album of the artist         |
| `/artist/{artist_id}/albums/{id}`                 | DELETE          | ✅        | Deleted album ID                   |
| `/artist/{artist_id}/album/{id}/songs`            | GET             | ❌        | All songs of an album of an artist |
| `/artist/{artist_id}/album/{id}/song`             | POST            | ✅        | New song added to album of artist  |
| `/artist/{artist_id}/album/{album_id}/song/{id}`  | PATCH           | ✅        | Edited song of the album of artist |
| `/artist/{artist_id}/albums/{album_id}/song/{id}` | DELETE          | ✅        | Deleted song ID                    |
| `/members`                                        | GET             | ❌        | All members                        |
| `/member`                                         | POST            | ✅        | New member added                   |
| `/member/{id}`                                    | PATCH           | ✅        | Edited member                      |
| `/member/{id}`                                    | DELETE          | ✅        | Deleted member ID                  |
| `/albums`                                         | GET             | ❌        | All albums                         |
| `/album`                                          | POST            | ✅        | New album added                    |
| `/album/{id}`                                     | PATCH           | ✅        | Edited album                       |
| `/album/{id}`                                     | DELETE          | ✅        | Deleted album ID                   |
| `/album/{id}/songs`                               | GET             | ❌        | All songs of an album              |
| `/album/{id}/song`                                | POST            | ✅        | New song added to an album         |
| `/album/{album_id}/song/{id}`                     | PATCH           | ✅        | Edited song of an album            |
| `/album/{album_id}/song/{id}`                     | DELETE          | ✅        | Deleted song ID                    |
| `/songs`                                          | GET             | ❌        | All songs                          |
| `/song`                                           | POST            | ✅        | New song added                     |
| `/song/{id}`                                      | PATCH           | ✅        | Edited song                        |
| `/song/{id}`                                      | DELETE          | ✅        | Deleted song ID                    |
